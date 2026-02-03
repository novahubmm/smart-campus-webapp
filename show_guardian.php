<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::where('email', 'ereggg@gmail.com')
    ->with(['guardianProfile.students.user', 'guardianProfile.students.grade', 'guardianProfile.students.classModel'])
    ->first();

if (!$user) {
    echo "Guardian not found\n";
    exit(1);
}

echo "Guardian: {$user->name}\n";
echo "Email: {$user->email}\n";
echo "Phone: " . ($user->phone ?? 'N/A') . "\n\n";

if (!$user->guardianProfile) {
    echo "No guardian profile found\n";
    exit(1);
}

$students = $user->guardianProfile->students;
echo "Students ({$students->count()}):\n";
echo str_repeat('-', 80) . "\n";

foreach ($students as $i => $student) {
    echo ($i + 1) . ". {$student->user->name}\n";
    echo "   Student ID: {$student->student_id}\n";
    echo "   Email: {$student->user->email}\n";
    echo "   Grade: {$student->grade->name}\n";
    echo "   Class: {$student->classModel->name}\n";
    echo "   Gender: {$student->gender}\n";
    echo "   DOB: {$student->dob}\n";
    echo "   Status: {$student->status}\n\n";
}
