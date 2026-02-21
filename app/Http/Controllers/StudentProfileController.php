<?php

namespace App\Http\Controllers;

use App\Models\StudentProfile;
use App\Http\Requests\StudentProfile\StudentProfileStoreRequest;
use App\Http\Requests\StudentProfile\StudentProfileUpdateRequest;
use App\DTOs\StudentProfile\StudentProfileStoreData;
use App\DTOs\StudentProfile\StudentProfileUpdateData;
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

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('student-photos', 'public');
            $validated['photo_path'] = $path;
        }

        $data = StudentProfileStoreData::from($validated);
        $profile = $this->studentProfileService->store($data);

        // Handle guardian linking or creation
        if (!empty($validated['existing_guardian_id'])) {
            // Link existing guardian to student
            try {
                $profile->guardians()->attach($validated['existing_guardian_id'], [
                    'relationship' => 'parent', // Default relationship
                ]);
            } catch (\Exception $e) {
                \Log::error('Guardian linking failed', [
                    'student_id' => $profile->id,
                    'guardian_id' => $validated['existing_guardian_id'],
                    'error' => $e->getMessage(),
                ]);
            }
        } elseif (!empty($validated['guardian_name']) && !empty($validated['guardian_email'])) {
            // Create new guardian profile
            try {
                // Create guardian user account
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
                    'status' => 'active',
                ]);

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

        return view('student-profiles.edit', compact('studentProfile', 'grades', 'classes', 'studentUsers'));
    }

    public function update(StudentProfileUpdateRequest $request, StudentProfile $studentProfile): RedirectResponse
    {
        $this->authorize('manage student profiles');

        $validated = $request->validated();

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($studentProfile->photo_path) {
                Storage::disk('public')->delete($studentProfile->photo_path);
            }
            
            // Store new photo
            $path = $request->file('photo')->store('student-photos', 'public');
            $validated['photo_path'] = $path;
        }

        $data = StudentProfileUpdateData::from($studentProfile, $validated);
        $this->studentProfileService->update($data);

        $this->logUpdate('StudentProfile', $studentProfile->id, $studentProfile->student_identifier);

        return redirect()->route('student-profiles.index')
            ->with('success', 'Student profile updated successfully.');
    }
}
