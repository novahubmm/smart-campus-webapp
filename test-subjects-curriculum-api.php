#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\GuardianProfile;
use App\Models\User;
use App\Repositories\Guardian\GuardianExamRepository;

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║       Subjects & Curriculum API Test Script               ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

$phone = $argv[1] ?? '09123456789';

$user = User::where('phone', $phone)->first();

if (!$user) {
    echo "❌ User not found with phone: {$phone}\n";
    exit(1);
}

$guardian = $user->guardianProfile;

if (!$guardian) {
    echo "❌ No guardian profile found\n";
    exit(1);
}

echo "👤 Guardian: {$user->name}\n";
echo "📱 Phone: {$user->phone}\n";
echo "\n";

$students = $guardian->students()->with(['user', 'grade', 'classModel'])->get();

if ($students->isEmpty()) {
    echo "❌ No students found for this guardian\n";
    exit(1);
}

$student = $students->first();
echo "👨‍🎓 Testing with student: {$student->user->name}\n";
echo "   Student ID: {$student->id}\n";
echo "   Grade: {$student->grade?->name}\n";
echo "\n";

$repository = new GuardianExamRepository();

// Test 1: Get Subjects List
echo "📚 Test 1: Get Subjects List\n";
echo "─────────────────────────────────────────────────────────────\n";
try {
    $subjects = $repository->getSubjects($student);
    echo "✅ Found " . count($subjects) . " subject(s)\n\n";
    
    foreach ($subjects as $index => $subject) {
        echo ($index + 1) . ". {$subject['name']}\n";
        echo "   ID: {$subject['id']}\n";
        echo "   Icon: {$subject['icon']}\n";
        echo "   Teacher: {$subject['teacher']}\n";
        echo "   Marks: {$subject['current_marks']}/{$subject['total_marks']}\n";
        echo "   Chapters: {$subject['completed_chapters']}/{$subject['total_chapters']}\n";
        echo "   Progress: {$subject['progress_percentage']}%\n";
        echo "\n";
    }
    
    // Save first subject for curriculum test
    $firstSubject = $subjects[0] ?? null;
    
} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n\n";
    $firstSubject = null;
}

// Test 2: Get Subject Curriculum
if ($firstSubject) {
    echo "📖 Test 2: Get Subject Curriculum for '{$firstSubject['name']}'\n";
    echo "─────────────────────────────────────────────────────────────\n";
    try {
        $curriculum = $repository->getSubjectCurriculum($firstSubject['id'], $student);
        
        echo "✅ Curriculum Data:\n";
        echo "   Subject: {$curriculum['name']}\n";
        echo "   Icon: {$curriculum['icon']}\n";
        echo "   Teacher: {$curriculum['teacher']}\n";
        echo "   Total Chapters: {$curriculum['total_chapters']}\n";
        echo "   Completed Chapters: {$curriculum['completed_chapters']}\n";
        echo "   Progress: {$curriculum['progress_percentage']}%\n";
        echo "   Total Topics: {$curriculum['total_topics']}\n";
        echo "   Completed Topics: {$curriculum['completed_topics']}\n";
        echo "\n";
        
        echo "📑 Chapters:\n";
        foreach ($curriculum['chapters'] as $index => $chapter) {
            echo "\n" . ($index + 1) . ". Chapter {$chapter['number']}: {$chapter['title']}\n";
            echo "   Status: {$chapter['status']}\n";
            echo "   Topics: {$chapter['completed_topics']}/{$chapter['total_topics']}\n";
            echo "   Progress: {$chapter['progress_percentage']}%\n";
            
            if (isset($chapter['current_topic'])) {
                echo "   Current Topic: {$chapter['current_topic']}\n";
            }
            
            if (!empty($chapter['topics'])) {
                echo "   Topics List:\n";
                foreach ($chapter['topics'] as $topic) {
                    $statusIcon = match($topic['status']) {
                        'completed' => '✅',
                        'current' => '🔄',
                        'in_progress' => '⏳',
                        default => '⭕'
                    };
                    echo "     {$statusIcon} {$topic['name']} ({$topic['status']})\n";
                }
            }
            
            if (!empty($chapter['related_items'])) {
                echo "   Related Items:\n";
                foreach ($chapter['related_items'] as $item) {
                    $icon = $item['type'] === 'exam' ? '📝' : '📚';
                    echo "     {$icon} {$item['title']}";
                    if (isset($item['score'])) {
                        echo " - Score: {$item['score']}";
                    }
                    if (isset($item['due_date'])) {
                        echo " - Due: {$item['due_date']}";
                    }
                    if (isset($item['date'])) {
                        echo " - Date: {$item['date']}";
                    }
                    echo "\n";
                }
            }
        }
        
    } catch (\Exception $e) {
        echo "❌ Error: {$e->getMessage()}\n";
        echo "   Stack trace: {$e->getTraceAsString()}\n";
    }
}

echo "\n";
echo "─────────────────────────────────────────────────────────────\n";
echo "\n";

$baseUrl = config('app.url');

echo "🔗 API Endpoints to test:\n";
echo "\n";
echo "1️⃣  Get Subjects List:\n";
echo "   GET {$baseUrl}/api/v1/guardian/students/{$student->id}/subjects\n";
echo "   Authorization: Bearer {access_token}\n";
echo "\n";

if ($firstSubject) {
    echo "2️⃣  Get Subject Curriculum:\n";
    echo "   GET {$baseUrl}/api/v1/guardian/students/{$student->id}/curriculum/subjects/{$firstSubject['id']}\n";
    echo "   Authorization: Bearer {access_token}\n";
    echo "\n";
}

echo "💡 Expected Response Format:\n";
echo "─────────────────────────────────────────────────────────────\n";
echo "Subjects List:\n";
echo json_encode([
    'success' => true,
    'message' => 'Subjects retrieved successfully',
    'data' => [
        [
            'id' => '1',
            'name' => 'Mathematics',
            'icon' => '🔢',
            'teacher' => 'U Kyaw Min',
            'teacher_id' => 'T001',
            'current_marks' => 65,
            'total_marks' => 100,
            'total_chapters' => 6,
            'completed_chapters' => 4,
            'progress_percentage' => 66.67,
        ]
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

echo "\n\n";
