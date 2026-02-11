<?php

/**
 * My Classes Screen API Test Suite
 * 
 * Tests all endpoints required for the My Classes (Class Info) screen
 * 
 * Usage:
 * php test-my-classes-api.php
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\GuardianProfile;
use App\Models\SchoolClass;
use App\Models\TeacherProfile;
use App\Models\GradeSubject;
use App\Models\Subject;
use App\Models\Timetable;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test configuration
$baseUrl = config('app.url');
$testResults = [];
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

// ANSI color codes
$GREEN = "\033[0;32m";
$RED = "\033[0;31m";
$YELLOW = "\033[1;33m";
$BLUE = "\033[0;34m";
$NC = "\033[0m"; // No Color

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         MY CLASSES SCREEN API TEST SUITE                  ║\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "\n";

// Helper function to make API requests
function makeApiRequest($method, $url, $token = null, $data = null) {
    $ch = curl_init();
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'body' => json_decode($response, true),
        'raw' => $response
    ];
}

// Helper function to log test result
function logTest($name, $passed, $message = '', $details = null) {
    global $testResults, $totalTests, $passedTests, $failedTests, $GREEN, $RED, $YELLOW, $NC;
    
    $totalTests++;
    if ($passed) {
        $passedTests++;
        echo "{$GREEN}✓{$NC} {$name}\n";
    } else {
        $failedTests++;
        echo "{$RED}✗{$NC} {$name}\n";
        if ($message) {
            echo "  {$YELLOW}→{$NC} {$message}\n";
        }
    }
    
    $testResults[] = [
        'name' => $name,
        'passed' => $passed,
        'message' => $message,
        'details' => $details
    ];
}

// Step 1: Get test data
echo "{$BLUE}━━━ Step 1: Preparing Test Data ━━━{$NC}\n\n";

try {
    // Find a guardian with students
    $guardian = GuardianProfile::whereHas('students')->with('user')->first();
    
    if (!$guardian) {
        echo "{$RED}✗ No guardian found with students. Please seed the database first.{$NC}\n";
        exit(1);
    }
    
    $student = $guardian->students()->with(['classModel', 'grade'])->first();
    
    if (!$student) {
        echo "{$RED}✗ No student found for guardian. Please seed the database first.{$NC}\n";
        exit(1);
    }
    
    echo "{$GREEN}✓{$NC} Found test guardian: {$guardian->user->name} (ID: {$guardian->id})\n";
    echo "{$GREEN}✓{$NC} Found test student: {$student->user->name} (ID: {$student->id})\n";
    $gradeName = $student->classModel && $student->classModel->grade ? $student->classModel->grade->name : 'N/A';
    $section = $student->classModel ? $student->classModel->section : 'N/A';
    echo "{$GREEN}✓{$NC} Student class: {$gradeName} - {$section}\n";
    echo "\n";
    
} catch (\Exception $e) {
    echo "{$RED}✗ Error preparing test data: {$e->getMessage()}{$NC}\n";
    exit(1);
}


// Step 2: Login to get access token
echo "{$BLUE}━━━ Step 2: Authentication ━━━{$NC}\n\n";

$loginResponse = makeApiRequest(
    'POST',
    "{$baseUrl}/api/v1/guardian/auth/login",
    null,
    [
        'email' => 'guardian1@smartcampusedu.com',
        'password' => 'password123'
    ]
);

if ($loginResponse['status'] === 200 && isset($loginResponse['body']['data']['token'])) {
    $accessToken = $loginResponse['body']['data']['token'];
    logTest('Guardian login successful', true);
    echo "  Token: " . substr($accessToken, 0, 20) . "...\n";
    
    // Get student ID from login response
    if (isset($loginResponse['body']['data']['user']['students']) && count($loginResponse['body']['data']['user']['students']) > 0) {
        $studentData = $loginResponse['body']['data']['user']['students'][0];
        $student = (object)$studentData;
        echo "  Using student: {$student->name} (ID: {$student->id})\n";
    }
} else {
    logTest('Guardian login failed', false, 'Could not authenticate guardian');
    echo "{$RED}Response: " . json_encode($loginResponse['body'], JSON_PRETTY_PRINT) . "{$NC}\n";
    exit(1);
}

if ($loginResponse['status'] === 200 && isset($loginResponse['body']['data']['token'])) {
    $accessToken = $loginResponse['body']['data']['token'];
    logTest('Guardian login successful', true);
    echo "  Token: " . substr($accessToken, 0, 20) . "...\n";
} else {
    logTest('Guardian login failed', false, 'Could not authenticate guardian');
    echo "{$RED}Response: " . json_encode($loginResponse['body'], JSON_PRETTY_PRINT) . "{$NC}\n";
    exit(1);
}

echo "\n";

// Step 3: Test Class Info Endpoint
echo "{$BLUE}━━━ Step 3: Testing Class Info Endpoint ━━━{$NC}\n\n";

$classInfoUrl = "{$baseUrl}/api/v1/guardian/students/{$student->id}/class";
echo "URL: GET {$classInfoUrl}\n\n";

$classInfoResponse = makeApiRequest('GET', $classInfoUrl, $accessToken);

if ($classInfoResponse['status'] === 200) {
    logTest('GET /students/{id}/class - Status 200', true);
    
    $data = $classInfoResponse['body']['data'] ?? null;
    
    if ($data) {
        // Validate required fields
        $requiredFields = [
            'class_id', 'grade_code', 'grade_name', 'grade', 'section',
            'academic_year', 'building', 'room_number', 'location',
            'student_count', 'total_capacity', 'class_teacher_name'
        ];
        
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $missingFields[] = $field;
            }
        }
        
        if (empty($missingFields)) {
            logTest('Class info contains all required fields', true);
        } else {
            logTest('Class info missing fields', false, 'Missing: ' . implode(', ', $missingFields));
        }
        
        // Display sample data
        echo "\n  Sample Response:\n";
        echo "  ├─ Grade Code: {$data['grade_code']}\n";
        echo "  ├─ Grade Name: {$data['grade_name']}\n";
        echo "  ├─ Academic Year: {$data['academic_year']}\n";
        echo "  ├─ Location: {$data['location']}\n";
        echo "  ├─ Student Count: {$data['student_count']}\n";
        echo "  └─ Class Teacher: {$data['class_teacher_name']}\n";
    } else {
        logTest('Class info response has data', false, 'No data in response');
    }
} else {
    logTest('GET /students/{id}/class', false, "Status: {$classInfoResponse['status']}");
}

echo "\n";

// Step 4: Test Class Teachers Endpoint
echo "{$BLUE}━━━ Step 4: Testing Class Teachers Endpoint ━━━{$NC}\n\n";

$teachersUrl = "{$baseUrl}/api/v1/guardian/students/{$student->id}/class/teachers";
echo "URL: GET {$teachersUrl}\n\n";

$teachersResponse = makeApiRequest('GET', $teachersUrl, $accessToken);

if ($teachersResponse['status'] === 200) {
    logTest('GET /students/{id}/class/teachers - Status 200', true);
    
    $data = $teachersResponse['body']['data'] ?? null;
    
    if ($data) {
        // Validate structure
        if (isset($data['class_teacher'])) {
            logTest('Response contains class_teacher', true);
            
            $ct = $data['class_teacher'];
            if ($ct) {
                echo "\n  Class Teacher:\n";
                echo "  ├─ Name: {$ct['name']}\n";
                echo "  ├─ Name (MM): {$ct['name_mm']}\n";
                echo "  ├─ Role: {$ct['role']}\n";
                echo "  ├─ Phone: {$ct['phone']}\n";
                echo "  ├─ Email: {$ct['email']}\n";
                echo "  └─ Subjects: " . implode(', ', $ct['subjects'] ?? []) . "\n";
            }
        } else {
            logTest('Response contains class_teacher', false);
        }
        
        if (isset($data['subject_teachers'])) {
            logTest('Response contains subject_teachers', true);
            echo "\n  Subject Teachers: " . count($data['subject_teachers']) . " teachers\n";
            
            foreach (array_slice($data['subject_teachers'], 0, 3) as $index => $teacher) {
                echo "  " . ($index + 1) . ". {$teacher['name']} - " . implode(', ', $teacher['subjects'] ?? []) . "\n";
            }
        } else {
            logTest('Response contains subject_teachers', false);
        }
        
        if (isset($data['total_teachers'])) {
            logTest('Response contains total_teachers', true);
            echo "\n  Total Teachers: {$data['total_teachers']}\n";
        } else {
            logTest('Response contains total_teachers', false);
        }
    } else {
        logTest('Teachers response has data', false, 'No data in response');
    }
} else {
    logTest('GET /students/{id}/class/teachers', false, "Status: {$teachersResponse['status']}");
}

echo "\n";

// Step 5: Test Subjects Endpoint
echo "{$BLUE}━━━ Step 5: Testing Subjects Endpoint ━━━{$NC}\n\n";

$subjectsUrl = "{$baseUrl}/api/v1/guardian/students/{$student->id}/subjects";
echo "URL: GET {$subjectsUrl}\n\n";

$subjectsResponse = makeApiRequest('GET', $subjectsUrl, $accessToken);

if ($subjectsResponse['status'] === 200) {
    logTest('GET /students/{id}/subjects - Status 200', true);
    
    $data = $subjectsResponse['body']['data'] ?? null;
    
    if ($data && isset($data['subjects'])) {
        logTest('Response contains subjects array', true);
        
        $subjects = $data['subjects'];
        echo "\n  Total Subjects: " . count($subjects) . "\n\n";
        
        if (count($subjects) > 0) {
            $firstSubject = $subjects[0];
            
            // Validate subject structure
            $requiredSubjectFields = [
                'id', 'name', 'name_mm', 'code', 'teacher_name',
                'color', 'icon', 'weekly_hours'
            ];
            
            $missingFields = [];
            foreach ($requiredSubjectFields as $field) {
                if (!isset($firstSubject[$field])) {
                    $missingFields[] = $field;
                }
            }
            
            if (empty($missingFields)) {
                logTest('Subject contains all required fields', true);
            } else {
                logTest('Subject missing fields', false, 'Missing: ' . implode(', ', $missingFields));
            }
            
            // Display sample subjects
            foreach (array_slice($subjects, 0, 3) as $index => $subject) {
                echo "  " . ($index + 1) . ". {$subject['name']} ({$subject['code']})\n";
                echo "     ├─ Teacher: {$subject['teacher_name']}\n";
                echo "     ├─ Weekly Hours: {$subject['weekly_hours']}\n";
                echo "     └─ Color: {$subject['color']}\n";
            }
        }
        
        if (isset($data['total_subjects'])) {
            logTest('Response contains total_subjects', true);
        }
        
        if (isset($data['total_weekly_hours'])) {
            logTest('Response contains total_weekly_hours', true);
            echo "\n  Total Weekly Hours: {$data['total_weekly_hours']}\n";
        }
    } else {
        logTest('Subjects response has data', false, 'No subjects in response');
    }
} else {
    logTest('GET /students/{id}/subjects', false, "Status: {$subjectsResponse['status']}");
}

echo "\n";

// Step 6: Test Timetable Endpoint
echo "{$BLUE}━━━ Step 6: Testing Timetable Endpoint ━━━{$NC}\n\n";

$timetableUrl = "{$baseUrl}/api/v1/guardian/students/{$student->id}/timetable";
echo "URL: GET {$timetableUrl}\n\n";

$timetableResponse = makeApiRequest('GET', $timetableUrl, $accessToken);

if ($timetableResponse['status'] === 200) {
    logTest('GET /students/{id}/timetable - Status 200', true);
    
    $data = $timetableResponse['body']['data'] ?? null;
    
    if ($data) {
        // Validate structure
        if (isset($data['week_start_date'])) {
            logTest('Response contains week_start_date', true);
            echo "\n  Week: {$data['week_start_date']} to {$data['week_end_date']}\n";
        }
        
        if (isset($data['schedule'])) {
            logTest('Response contains schedule', true);
            
            $schedule = $data['schedule'];
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            
            echo "\n  Schedule Overview:\n";
            foreach ($days as $day) {
                $periods = $schedule[$day] ?? [];
                echo "  ├─ {$day}: " . count($periods) . " periods\n";
                
                // Show first period as sample
                if (count($periods) > 0 && !isset($periods[0]['is_break'])) {
                    $period = $periods[0];
                    echo "     └─ First: {$period['subject_name']} ({$period['start_time']}-{$period['end_time']})\n";
                }
            }
        } else {
            logTest('Response contains schedule', false);
        }
        
        if (isset($data['total_periods_per_day'])) {
            logTest('Response contains total_periods_per_day', true);
            echo "\n  Total Periods Per Day: {$data['total_periods_per_day']}\n";
        }
        
        if (isset($data['break_times'])) {
            logTest('Response contains break_times', true);
            echo "\n  Break Times:\n";
            foreach ($data['break_times'] as $break) {
                echo "  ├─ {$break['name']}: {$break['start_time']}-{$break['end_time']}\n";
            }
        }
    } else {
        logTest('Timetable response has data', false, 'No data in response');
    }
} else {
    logTest('GET /students/{id}/timetable', false, "Status: {$timetableResponse['status']}");
}

echo "\n";

// Step 7: Test Class Statistics Endpoint
echo "{$BLUE}━━━ Step 7: Testing Class Statistics Endpoint ━━━{$NC}\n\n";

$statsUrl = "{$baseUrl}/api/v1/guardian/students/{$student->id}/class/statistics";
echo "URL: GET {$statsUrl}\n\n";

$statsResponse = makeApiRequest('GET', $statsUrl, $accessToken);

if ($statsResponse['status'] === 200) {
    logTest('GET /students/{id}/class/statistics - Status 200', true);
    
    $data = $statsResponse['body']['data'] ?? null;
    
    if ($data) {
        // Validate required fields
        $requiredFields = [
            'class_id', 'grade_code', 'total_students', 'male_students',
            'female_students', 'average_attendance', 'average_performance'
        ];
        
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $missingFields[] = $field;
            }
        }
        
        if (empty($missingFields)) {
            logTest('Statistics contains all required fields', true);
        } else {
            logTest('Statistics missing fields', false, 'Missing: ' . implode(', ', $missingFields));
        }
        
        // Display statistics
        echo "\n  Class Statistics:\n";
        echo "  ├─ Total Students: {$data['total_students']}\n";
        echo "  ├─ Male: {$data['male_students']}, Female: {$data['female_students']}\n";
        echo "  ├─ Average Attendance: {$data['average_attendance']}%\n";
        echo "  └─ Average Performance: {$data['average_performance']}%\n";
        
        if (isset($data['top_performers']) && count($data['top_performers']) > 0) {
            logTest('Statistics contains top_performers', true);
            echo "\n  Top Performers:\n";
            foreach ($data['top_performers'] as $performer) {
                echo "  {$performer['rank']}. {$performer['student_name']} - {$performer['average_score']}%\n";
            }
        }
        
        if (isset($data['subject_performance']) && count($data['subject_performance']) > 0) {
            logTest('Statistics contains subject_performance', true);
            echo "\n  Subject Performance (Top 3):\n";
            foreach (array_slice($data['subject_performance'], 0, 3) as $subject) {
                echo "  ├─ {$subject['subject_name']}\n";
                echo "  │  ├─ Class Average: {$subject['class_average']}\n";
                echo "  │  ├─ Highest: {$subject['highest_score']}\n";
                echo "  │  └─ Lowest: {$subject['lowest_score']}\n";
            }
        }
    } else {
        logTest('Statistics response has data', false, 'No data in response');
    }
} else {
    logTest('GET /students/{id}/class/statistics', false, "Status: {$statsResponse['status']}");
}

echo "\n";

// Step 8: Test with specific week date
echo "{$BLUE}━━━ Step 8: Testing Timetable with Week Parameter ━━━{$NC}\n\n";

$weekDate = Carbon::now()->startOfWeek()->format('Y-m-d');
$timetableWeekUrl = "{$baseUrl}/api/v1/guardian/students/{$student->id}/timetable?week_start_date={$weekDate}";
echo "URL: GET {$timetableWeekUrl}\n\n";

$timetableWeekResponse = makeApiRequest('GET', $timetableWeekUrl, $accessToken);

if ($timetableWeekResponse['status'] === 200) {
    logTest('GET /students/{id}/timetable?week_start_date - Status 200', true);
    
    $data = $timetableWeekResponse['body']['data'] ?? null;
    if ($data && isset($data['week_start_date'])) {
        if ($data['week_start_date'] === $weekDate) {
            logTest('Timetable returns correct week', true);
        } else {
            logTest('Timetable returns correct week', false, "Expected {$weekDate}, got {$data['week_start_date']}");
        }
    }
} else {
    logTest('GET /students/{id}/timetable?week_start_date', false, "Status: {$timetableWeekResponse['status']}");
}

echo "\n";

// Step 9: Test Error Handling - Invalid Student ID
echo "{$BLUE}━━━ Step 9: Testing Error Handling ━━━{$NC}\n\n";

$invalidStudentId = '00000000-0000-0000-0000-000000000000';
$errorUrl = "{$baseUrl}/api/v1/guardian/students/{$invalidStudentId}/class";
echo "URL: GET {$errorUrl}\n\n";

$errorResponse = makeApiRequest('GET', $errorUrl, $accessToken);

if ($errorResponse['status'] === 404) {
    logTest('Invalid student ID returns 404', true);
} else {
    logTest('Invalid student ID returns 404', false, "Got status: {$errorResponse['status']}");
}

echo "\n";

// Step 10: Test Unauthorized Access
echo "{$BLUE}━━━ Step 10: Testing Unauthorized Access ━━━{$NC}\n\n";

$unauthorizedUrl = "{$baseUrl}/api/v1/guardian/students/{$student->id}/class";
echo "URL: GET {$unauthorizedUrl} (without token)\n\n";

$unauthorizedResponse = makeApiRequest('GET', $unauthorizedUrl, null);

if ($unauthorizedResponse['status'] === 401) {
    logTest('Unauthorized request returns 401', true);
} else {
    logTest('Unauthorized request returns 401', false, "Got status: {$unauthorizedResponse['status']}");
}

echo "\n";

// Final Summary
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                    TEST SUMMARY                            ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "Total Tests:  {$totalTests}\n";
echo "{$GREEN}Passed:       {$passedTests}{$NC}\n";
echo "{$RED}Failed:       {$failedTests}{$NC}\n";
echo "Success Rate: " . ($totalTests > 0 ? round(($passedTests / $totalTests) * 100, 1) : 0) . "%\n";
echo "\n";

if ($failedTests > 0) {
    echo "{$YELLOW}Failed Tests:{$NC}\n";
    foreach ($testResults as $result) {
        if (!$result['passed']) {
            echo "  {$RED}✗{$NC} {$result['name']}\n";
            if ($result['message']) {
                echo "    → {$result['message']}\n";
            }
        }
    }
    echo "\n";
}

// API Endpoints Summary
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║              API ENDPOINTS TESTED                          ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "1. GET /api/v1/guardian/students/{student_id}/class\n";
echo "   → Get basic class information\n\n";
echo "2. GET /api/v1/guardian/students/{student_id}/class/teachers\n";
echo "   → Get all class teachers\n\n";
echo "3. GET /api/v1/guardian/students/{student_id}/subjects\n";
echo "   → Get class subjects\n\n";
echo "4. GET /api/v1/guardian/students/{student_id}/timetable\n";
echo "   → Get class timetable\n\n";
echo "5. GET /api/v1/guardian/students/{student_id}/class/statistics\n";
echo "   → Get class statistics\n\n";

echo "Test completed at: " . date('Y-m-d H:i:s') . "\n";
echo "\n";

exit($failedTests > 0 ? 1 : 0);
