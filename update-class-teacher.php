<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');

use App\Models\SchoolClass;
use App\Models\TeacherProfile;
use App\Models\User;

// Find the class by ID provided in request
$classId = '019c64fe-5d3f-7166-8a16-bd438554914f';

// Try to find Kindergarten A class
$class = SchoolClass::where('name', 'Kindergarten A')
    ->orWhere('name', 'LIKE', '%Kindergarten%A%')
    ->first();

if (!$class) {
    echo "Class Kindergarten A not found. Searching for class ID: " . $classId . "\n";
    $class = SchoolClass::find($classId);
}

if (!$class) {
    echo "Class not found\n";
    exit(1);
}

echo "Found class: " . $class->name . " (ID: " . $class->id . ")\n";

// Find teacher by email
$teacher = User::where('email', 'konyeinchan@smartcampusedu.com')->first();

if (!$teacher) {
    echo "Teacher with email konyeinchan@smartcampusedu.com not found\n";
    // List available teachers
    $teachers = User::whereHas('roles', function ($query) {
        $query->where('name', 'teacher');
    })->get();
    echo "Available teachers:\n";
    foreach ($teachers as $t) {
        echo "  - " . $t->name . " (" . $t->email . ")\n";
    }
    exit(1);
}

echo "Found teacher: " . $teacher->name . " (Email: " . $teacher->email . ")\n";

// Get teacher profile
$teacherProfile = TeacherProfile::where('user_id', $teacher->id)->first();

if (!$teacherProfile) {
    echo "Teacher profile not found for user: " . $teacher->id . "\n";
    exit(1);
}

echo "Found teacher profile (ID: " . $teacherProfile->id . ")\n";

// Update the class with the teacher
$class->teacher_id = $teacherProfile->id;
$class->save();

echo "Successfully updated class teacher!\n";
echo "Class: " . $class->name . "\n";
echo "Teacher: " . $teacher->name . "\n";
