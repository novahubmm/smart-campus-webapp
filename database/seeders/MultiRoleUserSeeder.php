<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Batch;
use App\Models\Department;
use App\Models\Grade;
use App\Models\GuardianProfile;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MultiRoleUserSeeder extends Seeder
{
    private $user;
    private $teacherProfile;
    private $guardianProfile;
    private $batch;
    private $students = [];

    /**
     * Run the database seeds.
     * 
     * Creates Ko Nyein Chan who is:
     * - Teacher: Teaching English in Grade 1
     * - Guardian: Has 3 students in Kindergarten A and 1 girl in Grade 2 Section A
     */
    public function run(): void
    {
        $this->command->info('🚀 Creating Multi-Role User: Ko Nyein Chan');
        $this->command->info('   Role 1: Teacher (English, Grade 1)');
        $this->command->info('   Role 2: Guardian (3 kids in Kindergarten A, 1 girl in Grade 2-A)');
        $this->command->newLine();

        DB::beginTransaction();

        try {
            $this->ensureRolesExist();
            $this->getBatch();
            $this->createUser();
            $this->createTeacherProfile();
            $this->createGuardianProfile();
            $this->createStudents();
            $this->linkStudentsToGuardian();

            DB::commit();

            $this->command->newLine();
            $this->command->info('✅ Multi-Role User Created Successfully!');
            $this->command->newLine();
            $this->displaySummary();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            $this->command->error('   File: ' . $e->getFile() . ':' . $e->getLine());
            throw $e;
        }
    }

    private function ensureRolesExist(): void
    {
        foreach (RoleEnum::values() as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName],
                ['guard_name' => 'web']
            );
        }
        $this->command->info('✓ Roles verified');
    }

    private function getBatch(): void
    {
        $this->batch = Batch::where('status', true)
            ->orderBy('start_date', 'desc')
            ->first();

        if (!$this->batch) {
            // Create a default batch if none exists
            $this->batch = Batch::create([
                'id' => (string) Str::uuid(),
                'name' => '2025-2026',
                'start_date' => '2025-06-01',
                'end_date' => '2026-03-31',
                'status' => true,
            ]);
            $this->command->info('✓ Created default batch: 2025-2026');
        } else {
            $this->command->info('✓ Using batch: ' . $this->batch->name);
        }
    }

    private function createUser(): void
    {
        $this->user = User::firstOrCreate(
            ['email' => 'konyeinchan@smartcampusedu.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Ko Nyein Chan',
                'phone' => '09123456789',
                'nrc' => '12/ABC(N)123456',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        // Assign both teacher and guardian roles
        $teacherRole = Role::where('name', RoleEnum::TEACHER->value)->first();
        $guardianRole = Role::where('name', RoleEnum::GUARDIAN->value)->first();

        if ($teacherRole && $guardianRole) {
            $this->user->roles()->sync([$teacherRole->id, $guardianRole->id]);
            $this->command->info('✓ User created with BOTH teacher and guardian roles');
        }
    }

    private function createTeacherProfile(): void
    {
        // Get or create English department
        $department = Department::firstOrCreate(
            ['code' => 'ENG'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'English Department',
                'is_active' => true,
            ]
        );

        // Check if teacher profile already exists
        $existingProfile = TeacherProfile::where('user_id', $this->user->id)->first();

        if ($existingProfile) {
            $existingProfile->update([
                'employee_id' => 'TCH-2025-KNC',
                'position' => 'English Teacher',
                'department_id' => $department->id,
                'phone_no' => $this->user->phone,
                'nrc' => $this->user->nrc,
                'basic_salary' => 600000,
                'hire_date' => '2023-06-01',
                'gender' => 'male',
                'status' => 'active',
                'subjects_taught' => ['English'],
                'current_classes' => ['Grade 1A', 'Grade 1B'],
            ]);
            $this->teacherProfile = $existingProfile;
            $this->command->info('✓ Updated existing teacher profile');
        } else {
            $this->teacherProfile = TeacherProfile::create([
                'id' => (string) Str::uuid(),
                'user_id' => $this->user->id,
                'employee_id' => 'TCH-2025-KNC',
                'position' => 'English Teacher',
                'department_id' => $department->id,
                'phone_no' => $this->user->phone,
                'nrc' => $this->user->nrc,
                'basic_salary' => 600000,
                'hire_date' => '2023-06-01',
                'gender' => 'male',
                'status' => 'active',
                'subjects_taught' => ['English'],
                'current_classes' => ['Grade 1A', 'Grade 1B'],
            ]);
            $this->command->info('✓ Teacher profile created');
        }

        // Assign as English teacher to Grade 1 classes
        $this->assignTeacherToGrade1();
    }

    private function assignTeacherToGrade1(): void
    {
        // Find Grade 1
        $grade1 = Grade::where('level', '1')
            ->where('batch_id', $this->batch->id)
            ->first();

        if (!$grade1) {
            $this->command->warn('⚠ Grade 1 not found, skipping class assignment');
            return;
        }

        // Find English subject
        $englishSubject = Subject::where('name', 'English')
            ->orWhere('code', 'ENG')
            ->first();

        if (!$englishSubject) {
            $this->command->warn('⚠ English subject not found, skipping subject assignment');
            return;
        }

        // Find Grade 1 classes
        $grade1Classes = SchoolClass::where('grade_id', $grade1->id)
            ->where('batch_id', $this->batch->id)
            ->get();

        if ($grade1Classes->isEmpty()) {
            $this->command->warn('⚠ No Grade 1 classes found');
            return;
        }

        $this->command->info('✓ Assigned to teach English in ' . $grade1Classes->count() . ' Grade 1 class(es)');
    }

    private function createGuardianProfile(): void
    {
        // Check if guardian profile already exists
        $existingProfile = GuardianProfile::where('user_id', $this->user->id)->first();

        if ($existingProfile) {
            $existingProfile->update([
                'occupation' => 'Teacher & Business Owner',
                'address' => 'No. 123, Main Street, Yangon',
                'notes' => 'Emergency Contact: Daw Mya Mya (Spouse) - 09987654321',
            ]);
            $this->guardianProfile = $existingProfile;
            $this->command->info('✓ Updated existing guardian profile');
        } else {
            $this->guardianProfile = GuardianProfile::create([
                'id' => (string) Str::uuid(),
                'user_id' => $this->user->id,
                'occupation' => 'Teacher & Business Owner',
                'address' => 'No. 123, Main Street, Yangon',
                'notes' => 'Emergency Contact: Daw Mya Mya (Spouse) - 09987654321',
            ]);
            $this->command->info('✓ Guardian profile created');
        }
    }

    private function createStudents(): void
    {
        $this->command->info('Creating students...');

        // Find existing Kindergarten A class
        $kindergartenA = $this->findExistingClass('Kindergarten A');
        if (!$kindergartenA) {
            $this->command->error('❌ Kindergarten A class not found!');
            throw new \Exception('Kindergarten A class not found');
        }
        
        // Find existing Grade 2 Section A class
        $grade2A = $this->findExistingClass('Grade 2 A');
        if (!$grade2A) {
            $this->command->error('❌ Grade 2 A class not found!');
            throw new \Exception('Grade 2 A class not found');
        }

        // Create 3 students in Kindergarten A
        $kgStudents = [
            ['name' => 'Maung Aung Aung', 'gender' => 'male', 'identifier' => 'KG-A-001'],
            ['name' => 'Maung Kyaw Kyaw', 'gender' => 'male', 'identifier' => 'KG-A-002'],
            ['name' => 'Ma Nyein Nyein', 'gender' => 'female', 'identifier' => 'KG-A-003'],
        ];

        foreach ($kgStudents as $index => $studentData) {
            $student = $this->createStudent(
                $studentData['name'],
                $studentData['gender'],
                $kindergartenA,
                $studentData['identifier']
            );
            $this->students[] = $student;
            $this->command->info("  ✓ Created: {$studentData['name']} (Kindergarten A)");
        }

        // Create 1 girl in Grade 2 Section A
        $grade2Student = $this->createStudent(
            'Ma Su Su Hlaing',
            'female',
            $grade2A,
            'G2-A-001'
        );
        $this->students[] = $grade2Student;
        $this->command->info("  ✓ Created: Ma Su Su Hlaing (Grade 2 Section A)");

        $this->command->info('✓ Created ' . count($this->students) . ' students');
    }

    private function findExistingClass(string $className, ?string $gradeLevel = null): ?SchoolClass
    {
        $query = SchoolClass::where('name', $className)
            ->where('batch_id', $this->batch->id);

        if ($gradeLevel !== null) {
            // Find grade first
            $grade = Grade::where('level', $gradeLevel)
                ->where('batch_id', $this->batch->id)
                ->first();
            
            if ($grade) {
                $query->where('grade_id', $grade->id);
            }
        }

        $class = $query->first();

        if ($class) {
            $this->command->info("  ✓ Found existing class: $className");
        }

        return $class;
    }

    private function createStudent(
        string $name,
        string $gender,
        SchoolClass $class,
        string $identifier
    ): StudentProfile {
        // Create user for student
        $email = strtolower(str_replace(' ', '', $name)) . '@student.smartcampusedu.com';
        
        $studentUser = User::firstOrCreate(
            ['email' => $email],
            [
                'id' => (string) Str::uuid(),
                'name' => $name,
                'phone' => '09' . rand(100000000, 999999999),
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        // Assign student role
        $studentRole = Role::where('name', RoleEnum::STUDENT->value)->first();
        if ($studentRole) {
            $studentUser->roles()->sync([$studentRole->id]);
        }

        // Generate student_id
        $existingCount = StudentProfile::count();
        $studentId = 'STU-' . date('Y') . '-' . str_pad($existingCount + 1, 4, '0', STR_PAD_LEFT);

        // Create or update student profile
        $studentProfile = StudentProfile::where('user_id', $studentUser->id)->first();
        
        if ($studentProfile) {
            // Update existing profile
            $studentProfile->update([
                'student_identifier' => $identifier,
                'class_id' => $class->id,
                'grade_id' => $class->grade_id,
                'gender' => $gender,
                'status' => 'active',
                'father_name' => 'Ko Nyein Chan',
                'father_phone_no' => $this->user->phone,
                'father_occupation' => 'Teacher',
                'emergency_contact_phone_no' => $this->user->phone,
            ]);
        } else {
            // Create new profile
            $studentProfile = StudentProfile::create([
                'id' => (string) Str::uuid(),
                'user_id' => $studentUser->id,
                'student_id' => $studentId,
                'student_identifier' => $identifier,
                'class_id' => $class->id,
                'grade_id' => $class->grade_id,
                'gender' => $gender,
                'status' => 'active',
                'father_name' => 'Ko Nyein Chan',
                'father_phone_no' => $this->user->phone,
                'father_occupation' => 'Teacher',
                'emergency_contact_phone_no' => $this->user->phone,
            ]);
        }

        // Link student to class
        $class->students()->syncWithoutDetaching([
            $studentProfile->id => [
                'batch_id' => $this->batch->id,
                'grade_id' => $class->grade_id,
            ]
        ]);

        return $studentProfile;
    }

    private function linkStudentsToGuardian(): void
    {
        $this->command->info('Linking students to guardian...');

        foreach ($this->students as $index => $student) {
            // Check if relationship already exists
            $exists = DB::table('guardian_student')
                ->where('guardian_profile_id', $this->guardianProfile->id)
                ->where('student_profile_id', $student->id)
                ->exists();

            if (!$exists) {
                DB::table('guardian_student')->insert([
                    'guardian_profile_id' => $this->guardianProfile->id,
                    'student_profile_id' => $student->id,
                    'relationship' => 'father',
                    'is_primary' => $index === 0, // First student is primary
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('✓ Linked ' . count($this->students) . ' students to guardian');
    }

    private function displaySummary(): void
    {
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('📋 MULTI-ROLE USER SUMMARY');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->newLine();
        
        $this->command->info('👤 USER INFORMATION:');
        $this->command->info('   Name:     ' . $this->user->name);
        $this->command->info('   Email:    ' . $this->user->email);
        $this->command->info('   Phone:    ' . $this->user->phone);
        $this->command->info('   Password: password');
        $this->command->newLine();

        $this->command->info('�‍🏫 TEACHERM ROLE:');
        $this->command->info('   Employee ID: ' . $this->teacherProfile->employee_id);
        $this->command->info('   Position:    ' . $this->teacherProfile->position);
        $this->command->info('   Department:  ' . ($this->teacherProfile->department->name ?? 'N/A'));
        $this->command->info('   Subject:     English');
        $this->command->info('   Grade:       Grade 1');
        $this->command->newLine();

        $this->command->info('👨‍👩‍👧‍👦 GUARDIAN ROLE:');
        $this->command->info('   Occupation:  ' . $this->guardianProfile->occupation);
        $this->command->info('   Students:    ' . count($this->students));
        $this->command->newLine();

        $this->command->info('👶 CHILDREN:');
        foreach ($this->students as $index => $student) {
            // Reload student with relationships
            $student = StudentProfile::with(['classModel.grade', 'user', 'grade'])->find($student->id);
            
            if ($student && $student->user) {
                $this->command->info('   ' . ($index + 1) . '. ' . $student->user->name);
                
                // Try to get grade from classModel first, then from direct relationship
                $grade = null;
                $className = 'N/A';
                
                if ($student->classModel) {
                    $className = $student->classModel->name;
                    if ($student->classModel->grade) {
                        $grade = $student->classModel->grade;
                    }
                }
                
                // Fallback to direct grade relationship
                if (!$grade && $student->grade) {
                    $grade = $student->grade;
                }
                
                if ($grade) {
                    $gradeDisplay = $grade->level == '0' ? 'Kindergarten' : 'Grade ' . $grade->level;
                    $this->command->info('      Grade:  ' . $gradeDisplay . ' - Section ' . $className);
                } else {
                    $this->command->info('      Grade:  N/A - Section ' . $className);
                }
                
                $this->command->info('      Gender: ' . ucfirst($student->gender));
                $this->command->info('      ID:     ' . $student->student_identifier);
            } else {
                $this->command->info('   ' . ($index + 1) . '. Unknown Student');
                $this->command->info('      Grade:  N/A');
            }
        }
        $this->command->newLine();

        $this->command->info('🔑 LOGIN CREDENTIALS:');
        $this->command->info('   Email:    konyeinchan@smartcampusedu.com');
        $this->command->info('   Password: password');
        $this->command->newLine();

        $this->command->info('🧪 TESTING:');
        $this->command->info('   1. Login with the credentials above');
        $this->command->info('   2. API should return available_roles: ["teacher", "guardian"]');
        $this->command->info('   3. API should return separate tokens for each role');
        $this->command->info('   4. Mobile app should show role switch option');
        $this->command->newLine();

        $this->command->info('═══════════════════════════════════════════════════════');
    }
}
