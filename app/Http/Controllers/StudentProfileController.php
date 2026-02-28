<?php

namespace App\Http\Controllers;

use App\Models\StudentProfile;
use App\Http\Requests\StudentProfile\StudentProfileStoreRequest;
use App\Http\Requests\StudentProfile\StudentProfileUpdateRequest;
use App\DTOs\StudentProfile\StudentProfileStoreData;
use App\DTOs\StudentProfile\StudentProfileUpdateData;
use App\Services\Upload\FileUploadService;
use App\Services\StudentProfileService;
use App\Enums\RoleEnum;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;

class StudentProfileController extends Controller
{
    use AuthorizesRequests, LogsActivity;

    public function __construct(private readonly StudentProfileService $studentProfileService) {}

    public function index(Request $request): View
    {
        $this->authorize('manage student profiles');

        $filters = [
            'search' => $request->string('search')->toString(),
            'grade' => $request->string('grade')->toString(),
            'class' => $request->string('class')->toString(),
            'status' => $request->string('status')->toString(),
            'active' => $request->string('active')->toString(),
        ];

        $query = StudentProfile::with(['user', 'grade', 'classModel', 'guardians'])
            ->when($filters['search'], function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where(function ($inner) use ($search) {
                    $inner->where('student_identifier', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['grade'], fn($q) => $q->where('grade_id', $filters['grade']))
            ->when($filters['class'], fn($q) => $q->where('class_id', $filters['class']))
            ->when($filters['status'], fn($q) => $q->where('status', $filters['status']))
            ->when($filters['active'], function ($q) use ($filters) {
                if ($filters['active'] === 'active') {
                    $q->whereHas('user', fn($uq) => $uq->where('is_active', true));
                } elseif ($filters['active'] === 'inactive') {
                    $q->whereHas('user', fn($uq) => $uq->where('is_active', false));
                }
            });

        $students = $query->latest()->paginate(10)->withQueryString();

        $totals = [
            'all' => StudentProfile::count(),
            'active' => StudentProfile::where('status', 'active')->count(),
        ];

        $grades = Grade::orderBy('name')->get();
        $classes = SchoolClass::orderBy('name')->get();

        return view('student-profiles.index', compact('students', 'totals', 'filters', 'grades', 'classes'));
    }

    public function create(): View
    {
        $this->authorize('manage student profiles');

        $grades = Grade::orderBy('name')->get();
        $classes = SchoolClass::orderBy('name')->get();
        $studentUsers = User::role(RoleEnum::STUDENT->value)->orderBy('name')->get();
        $guardians = \App\Models\GuardianProfile::with('user')->get();

        return view('student-profiles.create', compact('grades', 'classes', 'studentUsers', 'guardians'));
    }

    public function store(StudentProfileStoreRequest $request): RedirectResponse
    {
        $this->authorize('manage student profiles');

        $validated = $request->validated();
        $uploadService = app(FileUploadService::class);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $path = $uploadService->storeOptimizedUploadedImage(
                $request->file('photo'),
                'student-photos',
                'public',
                'student_photo'
            );
            $validated['photo_path'] = $path;
        }

        $data = StudentProfileStoreData::from($validated);
        $profile = $this->studentProfileService->store($data);

        // Handle guardian linking or creation
        if (!empty($validated['existing_guardian_id'])) {
            // Link existing guardian to student
            try {
                // Ensure the guardian profile exists and user has guardian role
                $guardianProfile = \App\Models\GuardianProfile::find($validated['existing_guardian_id']);
                if ($guardianProfile && $guardianProfile->user) {
                    // Ensure user has guardian role
                    if (!$guardianProfile->user->hasRole(RoleEnum::GUARDIAN->value)) {
                        $guardianProfile->user->assignRole(RoleEnum::GUARDIAN->value);
                    }
                    
                    // Link guardian to student
                    $profile->guardians()->attach($validated['existing_guardian_id'], [
                        'relationship' => 'parent', // Default relationship
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Guardian linking failed', [
                    'student_id' => $profile->id,
                    'guardian_id' => $validated['existing_guardian_id'],
                    'error' => $e->getMessage(),
                ]);
            }
        } elseif (!empty($validated['guardian_name']) && !empty($validated['guardian_email'])) {
            // Create new guardian profile or use existing user
            try {
                // Check if user already exists
                $guardianUser = User::where('email', $validated['guardian_email'])->first();
                
                if ($guardianUser) {
                    // User exists, ensure they have guardian role
                    if (!$guardianUser->hasRole(RoleEnum::GUARDIAN->value)) {
                        $guardianUser->assignRole(RoleEnum::GUARDIAN->value);
                    }
                    
                    // Check if guardian profile exists
                    $guardianProfile = $guardianUser->guardianProfile;
                    if (!$guardianProfile) {
                        // Create guardian profile for existing user
                        $guardianProfile = \App\Models\GuardianProfile::create([
                            'user_id' => $guardianUser->id,
                        ]);
                    }
                } else {
                    // Create new guardian user account
                    $guardianUser = User::create([
                        'name' => $validated['guardian_name'],
                        'email' => $validated['guardian_email'],
                        'phone' => $validated['guardian_phone'] ?? null,
                        'password' => bcrypt('12345678'), // Default password
                        'is_active' => true,
                    ]);

                    // Assign guardian role
                    $guardianUser->assignRole(RoleEnum::GUARDIAN->value);

                    // Create guardian profile
                    $guardianProfile = \App\Models\GuardianProfile::create([
                        'user_id' => $guardianUser->id,
                    ]);
                }

                // Link guardian to student
                $profile->guardians()->attach($guardianProfile->id, [
                    'relationship' => 'parent', // Default relationship
                ]);

            } catch (\Exception $e) {
                \Log::error('Guardian creation failed', [
                    'student_id' => $profile->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->logCreate('StudentProfile', $profile->id ?? '', $validated['student_identifier'] ?? null);

        return redirect()->route('student-profiles.index')
            ->with('success', 'Student profile created successfully.');
    }

    public function show(StudentProfile $studentProfile): View
    {
        $this->authorize('manage student profiles');

        $studentProfile->load(['user', 'grade', 'classModel', 'guardians.user']);

        return view('student-profiles.show', compact('studentProfile'));
    }

    public function edit(StudentProfile $studentProfile): View
    {
        $this->authorize('manage student profiles');

        $studentProfile->load(['user', 'grade', 'classModel', 'guardians.user']);
        $grades = Grade::orderBy('name')->get();
        $classes = SchoolClass::orderBy('name')->get();
        $studentUsers = User::role(RoleEnum::STUDENT->value)->orderBy('name')->get();
        $guardians = \App\Models\GuardianProfile::with('user')->get();

        return view('student-profiles.edit', compact('studentProfile', 'grades', 'classes', 'studentUsers', 'guardians'));
    }

    public function update(StudentProfileUpdateRequest $request, StudentProfile $studentProfile): RedirectResponse
    {
        $this->authorize('manage student profiles');

        $validated = $request->validated();
        $uploadService = app(FileUploadService::class);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($studentProfile->photo_path) {
                Storage::disk('public')->delete($studentProfile->photo_path);
            }
            
            // Store new photo
            $path = $uploadService->storeOptimizedUploadedImage(
                $request->file('photo'),
                'student-photos',
                'public',
                'student_photo'
            );
            $validated['photo_path'] = $path;
        }

        $data = StudentProfileUpdateData::from($studentProfile, $validated);
        $this->studentProfileService->update($data);

        $this->logUpdate('StudentProfile', $studentProfile->id, $studentProfile->student_identifier);

        return redirect()->route('student-profiles.index')
            ->with('success', 'Student profile updated successfully.');
    }

    public function toggleStatus(Request $request, StudentProfile $studentProfile): RedirectResponse
    {
        $this->authorize('manage student profiles');

        $newStatus = $studentProfile->status === 'active' ? 'inactive' : 'active';
        $studentProfile->status = $newStatus;
        $studentProfile->save();

        $this->logUpdate('StudentProfile', $studentProfile->id, "Status changed to {$newStatus}: {$studentProfile->student_identifier}");

        $message = $newStatus === 'active'
            ? __('student_profiles.Student activated successfully.') 
            : __('student_profiles.Student deactivated successfully.');

        // Preserve pagination and filters
        $queryParams = $request->only(['page', 'search', 'grade', 'class', 'status']);
        
        return redirect()->route('student-profiles.index', $queryParams)->with('success', $message);
    }
}
