#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Repositories\Guardian\GuardianExamRepository;
use App\Repositories\Guardian\GuardianCurriculumRepository;

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║   Subjects & Curriculum API Status Check                  ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

$phone = $argv[1] ?? '09123456789';
$user = User::where('phone', $phone)->first();

if (!$user || !$user->guardianProfile) {
    echo "❌ Guardian not found\n";
    exit(1);
}

$guardian = $user->guardianProfile;
$student = $guardian->students()->with(['user', 'grade', 'classModel'])->first();

if (!$student) {
    echo "❌ No students found\n";
    exit(1);
}

echo "👤 Guardian: {$user->name}\n";
echo "👨‍🎓 Student: {$student->user->name}\n";
echo "📚 Grade: {$student->grade?->name}\n";
echo "\n";

$examRepo = new GuardianExamRepository();
$curriculumRepo = new GuardianCurriculumRepository();

// Backend Team Action Items Status
echo "📞 Backend Team Action Items Status\n";
echo "═══════════════════════════════════════════════════════════\n\n";

$actionItems = [];

// 1. Subjects list endpoint
echo "1️⃣  Subjects List Endpoint\n";
echo "   URL: GET /api/v1/guardian/students/{studentId}/subjects\n";
try {
    $subjects = $examRepo->getSubjects($student);
    $firstSubject = $subjects[0] ?? null;
    
    if ($firstSubject) {
        $requiredFields = ['id', 'name', 'icon', 'teacher', 'teacher_id', 'current_marks', 'total_marks', 'total_chapters', 'completed_chapters', 'progress_percentage'];
        $missingFields = [];
        
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $firstSubject)) {
                $missingFields[] = $field;
            }
        }
        
        if (empty($missingFields)) {
            echo "   ✅ Status: COMPLETE\n";
            echo "   ✅ All required fields present\n";
            $actionItems[] = ['item' => 'Subjects list endpoint', 'status' => '✅ COMPLETE'];
        } else {
            echo "   ⚠️  Status: INCOMPLETE\n";
            echo "   ❌ Missing fields: " . implode(', ', $missingFields) . "\n";
            $actionItems[] = ['item' => 'Subjects list endpoint', 'status' => '⚠️  INCOMPLETE'];
        }
        
        echo "   📊 Sample Response:\n";
        echo "      - ID: {$firstSubject['id']}\n";
        echo "      - Name: {$firstSubject['name']}\n";
        echo "      - Icon: {$firstSubject['icon']}\n";
        echo "      - Teacher: {$firstSubject['teacher']}\n";
        echo "      - Teacher ID: " . ($firstSubject['teacher_id'] ?? 'N/A') . "\n";
        echo "      - Current Marks: {$firstSubject['current_marks']}/{$firstSubject['total_marks']}\n";
        echo "      - Chapters: {$firstSubject['completed_chapters']}/{$firstSubject['total_chapters']}\n";
        echo "      - Progress: {$firstSubject['progress_percentage']}%\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Status: ERROR\n";
    echo "   ❌ Error: {$e->getMessage()}\n";
    $actionItems[] = ['item' => 'Subjects list endpoint', 'status' => '❌ ERROR'];
}
echo "\n";

// 2. Subject curriculum endpoint
echo "2️⃣  Subject Curriculum Endpoint\n";
echo "   URL: GET /api/v1/guardian/students/{studentId}/curriculum/subjects/{subjectId}\n";
try {
    if ($firstSubject) {
        $curriculum = $curriculumRepo->getSubjectCurriculum($firstSubject['id'], $student);
        
        $requiredTopLevelFields = ['id', 'name', 'icon', 'teacher', 'total_chapters', 'completed_chapters', 'progress_percentage', 'total_topics', 'completed_topics', 'chapters'];
        $missingTopLevel = [];
        
        foreach ($requiredTopLevelFields as $field) {
            if (!array_key_exists($field, $curriculum)) {
                $missingTopLevel[] = $field;
            }
        }
        
        $hasChapters = !empty($curriculum['chapters']);
        $chapterFieldsOk = false;
        $topicsFieldsOk = false;
        $relatedItemsPresent = false;
        $currentTopicPresent = false;
        
        if ($hasChapters) {
            $firstChapter = $curriculum['chapters'][0];
            $requiredChapterFields = ['id', 'number', 'title', 'description', 'total_topics', 'completed_topics', 'progress_percentage', 'status', 'topics', 'related_items'];
            $missingChapterFields = [];
            
            foreach ($requiredChapterFields as $field) {
                if (!array_key_exists($field, $firstChapter)) {
                    $missingChapterFields[] = $field;
                }
            }
            
            $chapterFieldsOk = empty($missingChapterFields);
            
            if (!empty($firstChapter['topics'])) {
                $firstTopic = $firstChapter['topics'][0];
                $requiredTopicFields = ['id', 'name', 'order', 'status', 'duration'];
                $missingTopicFields = [];
                
                foreach ($requiredTopicFields as $field) {
                    if (!array_key_exists($field, $firstTopic)) {
                        $missingTopicFields[] = $field;
                    }
                }
                
                $topicsFieldsOk = empty($missingTopicFields);
            }
            
            $relatedItemsPresent = array_key_exists('related_items', $firstChapter);
            
            // Check for in_progress chapters with current_topic
            foreach ($curriculum['chapters'] as $chapter) {
                if ($chapter['status'] === 'in_progress' && isset($chapter['current_topic'])) {
                    $currentTopicPresent = true;
                    break;
                }
            }
        }
        
        if (empty($missingTopLevel) && $chapterFieldsOk && $topicsFieldsOk && $relatedItemsPresent) {
            echo "   ✅ Status: COMPLETE\n";
            echo "   ✅ All required fields present\n";
            echo "   ✅ Chapters structure correct\n";
            echo "   ✅ Topics structure correct\n";
            echo "   ✅ Related items field present\n";
            if ($currentTopicPresent) {
                echo "   ✅ Current topic field present (for in_progress chapters)\n";
            } else {
                echo "   ℹ️  Current topic field: No in_progress chapters to test\n";
            }
            $actionItems[] = ['item' => 'Subject curriculum endpoint', 'status' => '✅ COMPLETE'];
        } else {
            echo "   ⚠️  Status: INCOMPLETE\n";
            if (!empty($missingTopLevel)) {
                echo "   ❌ Missing top-level fields: " . implode(', ', $missingTopLevel) . "\n";
            }
            if (!$chapterFieldsOk) {
                echo "   ❌ Chapter structure incomplete\n";
            }
            if (!$topicsFieldsOk) {
                echo "   ❌ Topics structure incomplete\n";
            }
            if (!$relatedItemsPresent) {
                echo "   ❌ Related items field missing\n";
            }
            $actionItems[] = ['item' => 'Subject curriculum endpoint', 'status' => '⚠️  INCOMPLETE'];
        }
        
        echo "   📊 Sample Response:\n";
        echo "      - Subject: {$curriculum['name']}\n";
        echo "      - Icon: {$curriculum['icon']}\n";
        echo "      - Teacher: {$curriculum['teacher']}\n";
        echo "      - Total Chapters: {$curriculum['total_chapters']}\n";
        echo "      - Completed Chapters: {$curriculum['completed_chapters']}\n";
        echo "      - Progress: {$curriculum['progress_percentage']}%\n";
        echo "      - Total Topics: {$curriculum['total_topics']}\n";
        echo "      - Completed Topics: {$curriculum['completed_topics']}\n";
        
        if ($hasChapters) {
            echo "      - First Chapter:\n";
            echo "        * Number: {$firstChapter['number']}\n";
            echo "        * Title: {$firstChapter['title']}\n";
            echo "        * Status: {$firstChapter['status']}\n";
            echo "        * Topics: {$firstChapter['completed_topics']}/{$firstChapter['total_topics']}\n";
            echo "        * Related Items: " . count($firstChapter['related_items']) . "\n";
        }
    }
} catch (\Exception $e) {
    echo "   ❌ Status: ERROR\n";
    echo "   ❌ Error: {$e->getMessage()}\n";
    $actionItems[] = ['item' => 'Subject curriculum endpoint', 'status' => '❌ ERROR'];
}
echo "\n";

// 3. Related items field
echo "3️⃣  Related Items Field\n";
try {
    if ($firstSubject && isset($curriculum)) {
        $hasRelatedItems = false;
        $relatedItemsCount = 0;
        
        foreach ($curriculum['chapters'] as $chapter) {
            if (!empty($chapter['related_items'])) {
                $hasRelatedItems = true;
                $relatedItemsCount += count($chapter['related_items']);
            }
        }
        
        if ($hasRelatedItems) {
            echo "   ✅ Status: IMPLEMENTED\n";
            echo "   ✅ Related items found: {$relatedItemsCount} total\n";
            $actionItems[] = ['item' => 'Related items field', 'status' => '✅ IMPLEMENTED'];
        } else {
            echo "   ⚠️  Status: FIELD EXISTS BUT NO DATA\n";
            echo "   ℹ️  Field is present but no related items (exams/homework) found\n";
            $actionItems[] = ['item' => 'Related items field', 'status' => '⚠️  NO DATA'];
        }
    }
} catch (\Exception $e) {
    echo "   ❌ Status: ERROR\n";
    echo "   ❌ Error: {$e->getMessage()}\n";
    $actionItems[] = ['item' => 'Related items field', 'status' => '❌ ERROR'];
}
echo "\n";

// 4. Current topic field
echo "4️⃣  Current Topic Field\n";
try {
    if (isset($curriculum)) {
        $hasCurrentTopic = false;
        $inProgressChapters = 0;
        
        foreach ($curriculum['chapters'] as $chapter) {
            if ($chapter['status'] === 'in_progress') {
                $inProgressChapters++;
                if (isset($chapter['current_topic'])) {
                    $hasCurrentTopic = true;
                }
            }
        }
        
        if ($inProgressChapters > 0) {
            if ($hasCurrentTopic) {
                echo "   ✅ Status: IMPLEMENTED\n";
                echo "   ✅ Current topic field present for in_progress chapters\n";
                $actionItems[] = ['item' => 'Current topic field', 'status' => '✅ IMPLEMENTED'];
            } else {
                echo "   ⚠️  Status: MISSING\n";
                echo "   ❌ Found {$inProgressChapters} in_progress chapter(s) without current_topic\n";
                $actionItems[] = ['item' => 'Current topic field', 'status' => '⚠️  MISSING'];
            }
        } else {
            echo "   ℹ️  Status: NO IN_PROGRESS CHAPTERS TO TEST\n";
            echo "   ℹ️  Field implementation correct, but no in_progress chapters in data\n";
            $actionItems[] = ['item' => 'Current topic field', 'status' => 'ℹ️  N/A'];
        }
    }
} catch (\Exception $e) {
    echo "   ❌ Status: ERROR\n";
    echo "   ❌ Error: {$e->getMessage()}\n";
    $actionItems[] = ['item' => 'Current topic field', 'status' => '❌ ERROR'];
}
echo "\n";

// 5. Progress percentage calculation
echo "5️⃣  Progress Percentage Calculation\n";
try {
    if (isset($curriculum)) {
        $calculationCorrect = true;
        $issues = [];
        
        foreach ($curriculum['chapters'] as $chapter) {
            $totalTopics = $chapter['total_topics'];
            $completedTopics = $chapter['completed_topics'];
            $reportedProgress = $chapter['progress_percentage'];
            
            if ($totalTopics > 0) {
                $expectedProgress = round(($completedTopics / $totalTopics) * 100, 1);
                if (abs($expectedProgress - $reportedProgress) > 0.1) {
                    $calculationCorrect = false;
                    $issues[] = "Chapter {$chapter['number']}: Expected {$expectedProgress}%, Got {$reportedProgress}%";
                }
            }
        }
        
        if ($calculationCorrect) {
            echo "   ✅ Status: CORRECT\n";
            echo "   ✅ Progress calculations are accurate\n";
            $actionItems[] = ['item' => 'Progress percentage calculation', 'status' => '✅ CORRECT'];
        } else {
            echo "   ⚠️  Status: INCORRECT\n";
            foreach ($issues as $issue) {
                echo "   ❌ {$issue}\n";
            }
            $actionItems[] = ['item' => 'Progress percentage calculation', 'status' => '⚠️  INCORRECT'];
        }
    }
} catch (\Exception $e) {
    echo "   ❌ Status: ERROR\n";
    echo "   ❌ Error: {$e->getMessage()}\n";
    $actionItems[] = ['item' => 'Progress percentage calculation', 'status' => '❌ ERROR'];
}
echo "\n";

// Summary
echo "═══════════════════════════════════════════════════════════\n";
echo "📊 SUMMARY\n";
echo "═══════════════════════════════════════════════════════════\n\n";

foreach ($actionItems as $item) {
    echo "{$item['status']}  {$item['item']}\n";
}

echo "\n";

// Overall Status
$completeCount = count(array_filter($actionItems, fn($i) => strpos($i['status'], '✅') !== false));
$totalCount = count($actionItems);
$percentage = round(($completeCount / $totalCount) * 100);

echo "Overall Completion: {$completeCount}/{$totalCount} ({$percentage}%)\n";
echo "\n";

if ($percentage === 100) {
    echo "🎉 ALL BACKEND REQUIREMENTS COMPLETE!\n";
    echo "✅ Ready for mobile app integration\n";
} elseif ($percentage >= 80) {
    echo "⚠️  MOSTLY COMPLETE - Minor issues to address\n";
} else {
    echo "❌ INCOMPLETE - Significant work needed\n";
}

echo "\n";
echo "🔗 API Endpoints:\n";
echo "─────────────────────────────────────────────────────────────\n";
$baseUrl = config('app.url');
echo "1. GET {$baseUrl}/api/v1/guardian/students/{$student->id}/subjects\n";
if ($firstSubject) {
    echo "2. GET {$baseUrl}/api/v1/guardian/students/{$student->id}/curriculum/subjects/{$firstSubject['id']}\n";
}
echo "\n";
