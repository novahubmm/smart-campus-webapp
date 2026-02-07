<?php

/**
 * Add Parent Portal APIs to Existing Postman Collection
 * Run: php add-parent-portal-apis.php
 */

$collectionFile = __DIR__ . '/UNIFIED_APP_POSTMAN_COLLECTION.json';
$collection = json_decode(file_get_contents($collectionFile), true);

// Add student_id variable if not exists
$hasStudentId = false;
foreach ($collection['variable'] as $var) {
    if ($var['key'] === 'student_id') {
        $hasStudentId = true;
        break;
    }
}

if (!$hasStudentId) {
    $collection['variable'][] = [
        "key" => "student_id",
        "value" => "",
        "type" => "string"
    ];
}

// Update version
$collection['info']['version'] = '2.0.0';
$collection['info']['description'] = 'Complete API collection for Unified Teacher-Guardian Mobile App with Parent Portal APIs';

// Helper function to create authenticated request
function createAuthRequest($method, $path, $body = null, $description = '') {
    $request = [
        "auth" => [
            "type" => "bearer",
            "bearer" => [
                ["key" => "token", "value" => "{{current_token}}", "type" => "string"]
            ]
        ],
        "method" => $method,
        "header" => [
            ["key" => "Accept", "value" => "application/json"]
        ],
        "url" => [
            "raw" => "{{base_url}}" . $path,
            "host" => ["{{base_url}}"],
            "path" => array_filter(explode('/', $path))
        ]
    ];
    
    if ($description) {
        $request['description'] = $description;
    }
    
    if ($body && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        $request['header'][] = ["key" => "Content-Type", "value" => "application/json"];
        $request['body'] = [
            "mode" => "raw",
            "raw" => json_encode($body, JSON_PRETTY_PRINT)
        ];
    }
    
    return ["request" => $request];
}

// 5. Parent Portal - Academic Performance
$collection['item'][] = [
    "name" => "5. Parent Portal - Academic",
    "description" => "Academic Performance APIs for Parent Portal",
    "item" => [
        array_merge(
            ["name" => "Get Academic Overview"],
            createAuthRequest("GET", "/guardian/academic/{{student_id}}", null, 
                "Get complete academic performance overview for a student")
        ),
        array_merge(
            ["name" => "Get Report Cards List"],
            createAuthRequest("GET", "/guardian/report-cards?student_id={{student_id}}", null,
                "Get list of all report cards for a student")
        ),
        array_merge(
            ["name" => "Get Report Card Detail"],
            createAuthRequest("GET", "/guardian/report-cards/RPT001?student_id={{student_id}}", null,
                "Get detailed information of a specific report card")
        ),
        array_merge(
            ["name" => "Download Report Card PDF"],
            createAuthRequest("GET", "/guardian/report-cards/RPT001/download", null,
                "Download report card as PDF file")
        )
    ]
];

// 6. Parent Portal - Exams
$collection['item'][] = [
    "name" => "6. Parent Portal - Exams",
    "description" => "Exam and Subject APIs for Parent Portal",
    "item" => [
        array_merge(
            ["name" => "Get All Exams"],
            createAuthRequest("GET", "/guardian/exams?student_id={{student_id}}&status=all", null,
                "Get all upcoming and completed exams for a student")
        ),
        array_merge(
            ["name" => "Get Upcoming Exams"],
            createAuthRequest("GET", "/guardian/exams?student_id={{student_id}}&status=upcoming", null,
                "Get only upcoming exams")
        ),
        array_merge(
            ["name" => "Get Completed Exams"],
            createAuthRequest("GET", "/guardian/exams?student_id={{student_id}}&status=completed", null,
                "Get only completed exams")
        ),
        array_merge(
            ["name" => "Get Exam Detail"],
            createAuthRequest("GET", "/guardian/exams/EXM001", null,
                "Get detailed information about a specific exam")
        ),
        array_merge(
            ["name" => "Get Exam Results"],
            createAuthRequest("GET", "/guardian/exams/EXM001/results?student_id={{student_id}}", null,
                "Get exam results and analysis for a student")
        ),
        array_merge(
            ["name" => "Get Subjects List"],
            createAuthRequest("GET", "/guardian/subjects?student_id={{student_id}}", null,
                "Get all subjects for a student")
        ),
        array_merge(
            ["name" => "Get Subject Detail"],
            createAuthRequest("GET", "/guardian/subjects/SUB001?student_id={{student_id}}", null,
                "Get detailed information about a subject")
        ),
        array_merge(
            ["name" => "Get Subject Performance"],
            createAuthRequest("GET", "/guardian/subjects/SUB001/performance?student_id={{student_id}}", null,
                "Get performance analysis for a specific subject")
        ),
        array_merge(
            ["name" => "Get Subject Schedule"],
            createAuthRequest("GET", "/guardian/subjects/SUB001/schedule?student_id={{student_id}}", null,
                "Get class schedule for a specific subject")
        )
    ]
];

// 7. Parent Portal - Leave Requests
$collection['item'][] = [
    "name" => "7. Parent Portal - Leave Requests",
    "description" => "Leave Request Management APIs",
    "item" => [
        array_merge(
            ["name" => "Get All Leave Requests"],
            createAuthRequest("GET", "/guardian/leave-requests?student_id={{student_id}}&status=all", null,
                "Get all leave requests for a student")
        ),
        array_merge(
            ["name" => "Get Pending Leave Requests"],
            createAuthRequest("GET", "/guardian/leave-requests?student_id={{student_id}}&status=pending", null,
                "Get only pending leave requests")
        ),
        array_merge(
            ["name" => "Get Leave Request Stats"],
            createAuthRequest("GET", "/guardian/leave-requests/stats?student_id={{student_id}}", null,
                "Get leave request statistics and balance")
        ),
        array_merge(
            ["name" => "Get Leave Request Detail"],
            createAuthRequest("GET", "/guardian/leave-requests/LR001?student_id={{student_id}}", null,
                "Get detailed information about a leave request")
        ),
        array_merge(
            ["name" => "Apply for Leave"],
            createAuthRequest("POST", "/guardian/leave-requests", [
                "student_id" => "{{student_id}}",
                "start_date" => "2026-02-15",
                "end_date" => "2026-02-17",
                "leave_type" => "sick",
                "reason" => "Medical appointment and recovery"
            ], "Submit a new leave request for a student")
        ),
        array_merge(
            ["name" => "Cancel Leave Request"],
            createAuthRequest("DELETE", "/guardian/leave-requests/LR001", null,
                "Cancel a pending leave request")
        ),
        array_merge(
            ["name" => "Get Leave Types"],
            createAuthRequest("GET", "/guardian/leave-types", null,
                "Get available leave types and their requirements")
        )
    ]
];

// 8. Parent Portal - School Fees
$collection['item'][] = [
    "name" => "8. Parent Portal - School Fees",
    "description" => "Fee Management and Payment APIs",
    "item" => [
        array_merge(
            ["name" => "Get All Fees"],
            createAuthRequest("GET", "/guardian/fees?student_id={{student_id}}&status=all", null,
                "Get all fee invoices for a student")
        ),
        array_merge(
            ["name" => "Get Unpaid Fees"],
            createAuthRequest("GET", "/guardian/fees?student_id={{student_id}}&status=unpaid", null,
                "Get only unpaid fee invoices")
        ),
        array_merge(
            ["name" => "Get Pending Fees"],
            createAuthRequest("GET", "/guardian/fees/pending?student_id={{student_id}}", null,
                "Get pending fee verification")
        ),
        array_merge(
            ["name" => "Get Fee Detail"],
            createAuthRequest("GET", "/guardian/fees/FEE001", null,
                "Get detailed information about a specific fee")
        ),
        array_merge(
            ["name" => "Get Payment History"],
            createAuthRequest("GET", "/guardian/fees/payment-history?student_id={{student_id}}", null,
                "Get payment history for a student")
        ),
        array_merge(
            ["name" => "Initiate Payment"],
            createAuthRequest("POST", "/guardian/fees/FEE001/payment", [
                "payment_method" => "bank_transfer",
                "amount" => 120000
            ], "Initiate payment for a fee invoice")
        )
    ]
];

// 9. Parent Portal - Student Profile
$collection['item'][] = [
    "name" => "9. Parent Portal - Student Profile",
    "description" => "Student Profile and Academic Summary APIs",
    "item" => [
        array_merge(
            ["name" => "Get Student Profile"],
            createAuthRequest("GET", "/guardian/students/{{student_id}}/profile", null,
                "Get complete student profile information")
        ),
        array_merge(
            ["name" => "Get Academic Summary"],
            createAuthRequest("GET", "/guardian/students/{{student_id}}/academic-summary", null,
                "Get academic performance summary")
        ),
        array_merge(
            ["name" => "Get Rankings"],
            createAuthRequest("GET", "/guardian/students/{{student_id}}/rankings", null,
                "Get student rankings and class position")
        ),
        array_merge(
            ["name" => "Get Achievements"],
            createAuthRequest("GET", "/guardian/students/{{student_id}}/achievements", null,
                "Get student achievements and awards")
        ),
        array_merge(
            ["name" => "Get Goals"],
            createAuthRequest("GET", "/guardian/students/{{student_id}}/goals", null,
                "Get student goals set by parent")
        ),
        array_merge(
            ["name" => "Create Goal"],
            createAuthRequest("POST", "/guardian/students/{{student_id}}/goals", [
                "type" => "gpa",
                "title" => "Achieve 3.8 GPA",
                "description" => "Improve overall GPA to 3.8 by end of term",
                "target_value" => 3.8,
                "current_value" => 3.5,
                "target_date" => "2026-06-30"
            ], "Create a new goal for student")
        ),
        array_merge(
            ["name" => "Update Goal"],
            createAuthRequest("PUT", "/guardian/students/{{student_id}}/goals/GOAL001", [
                "current_value" => 3.6,
                "status" => "in_progress"
            ], "Update an existing goal")
        ),
        array_merge(
            ["name" => "Delete Goal"],
            createAuthRequest("DELETE", "/guardian/students/{{student_id}}/goals/GOAL001", null,
                "Delete a goal")
        ),
        array_merge(
            ["name" => "Get Notes"],
            createAuthRequest("GET", "/guardian/students/{{student_id}}/notes", null,
                "Get parent notes about student")
        ),
        array_merge(
            ["name" => "Create Note"],
            createAuthRequest("POST", "/guardian/students/{{student_id}}/notes", [
                "title" => "Math Improvement",
                "content" => "Student showing good progress in mathematics. Need to focus more on geometry.",
                "category" => "academic"
            ], "Create a new note about student")
        ),
        array_merge(
            ["name" => "Update Note"],
            createAuthRequest("PUT", "/guardian/students/{{student_id}}/notes/NOTE001", [
                "content" => "Updated: Excellent progress in mathematics. Geometry skills improved significantly."
            ], "Update an existing note")
        ),
        array_merge(
            ["name" => "Delete Note"],
            createAuthRequest("DELETE", "/guardian/students/{{student_id}}/notes/NOTE001", null,
                "Delete a note")
        )
    ]
];

// 10. Parent Portal - Curriculum
$collection['item'][] = [
    "name" => "10. Parent Portal - Curriculum",
    "description" => "Curriculum Progress Tracking APIs",
    "item" => [
        array_merge(
            ["name" => "Get Curriculum Overview"],
            createAuthRequest("GET", "/guardian/curriculum?student_id={{student_id}}", null,
                "Get curriculum progress overview for all subjects")
        ),
        array_merge(
            ["name" => "Get Subject Curriculum"],
            createAuthRequest("GET", "/guardian/curriculum/subjects/SUB001?student_id={{student_id}}", null,
                "Get detailed curriculum progress for a specific subject")
        ),
        array_merge(
            ["name" => "Get Chapters"],
            createAuthRequest("GET", "/guardian/curriculum/chapters?student_id={{student_id}}&subject_id=SUB001", null,
                "Get chapter list and progress for a subject")
        ),
        array_merge(
            ["name" => "Get Chapter Detail"],
            createAuthRequest("GET", "/guardian/curriculum/chapters/CH001?student_id={{student_id}}", null,
                "Get detailed information about a specific chapter")
        )
    ]
];

// 11. Parent Portal - Class Information
$collection['item'][] = [
    "name" => "11. Parent Portal - Class Info",
    "description" => "Class Information and Timetable APIs",
    "item" => [
        array_merge(
            ["name" => "Get Class Information"],
            createAuthRequest("GET", "/guardian/class-info?student_id={{student_id}}", null,
                "Get class information including teachers and subjects")
        ),
        array_merge(
            ["name" => "Get Class Detail"],
            createAuthRequest("GET", "/guardian/classes/CLS001", null,
                "Get detailed class information")
        ),
        array_merge(
            ["name" => "Get Timetable"],
            createAuthRequest("GET", "/guardian/timetable?student_id={{student_id}}", null,
                "Get weekly timetable for student")
        ),
        array_merge(
            ["name" => "Get Day Timetable"],
            createAuthRequest("GET", "/guardian/timetable/day?student_id={{student_id}}&date=2026-02-07", null,
                "Get timetable for a specific day")
        )
    ]
];

// 12. Parent Portal - Attendance
$collection['item'][] = [
    "name" => "12. Parent Portal - Attendance",
    "description" => "Attendance Tracking APIs",
    "item" => [
        array_merge(
            ["name" => "Get Attendance Records"],
            createAuthRequest("GET", "/guardian/attendance?student_id={{student_id}}", null,
                "Get attendance records for student")
        ),
        array_merge(
            ["name" => "Get Attendance Summary"],
            createAuthRequest("GET", "/guardian/attendance/summary?student_id={{student_id}}", null,
                "Get attendance summary and statistics")
        ),
        array_merge(
            ["name" => "Get Attendance Calendar"],
            createAuthRequest("GET", "/guardian/attendance/calendar?student_id={{student_id}}&month=2&year=2026", null,
                "Get attendance calendar for a specific month")
        ),
        array_merge(
            ["name" => "Get Attendance Stats"],
            createAuthRequest("GET", "/guardian/attendance/stats?student_id={{student_id}}", null,
                "Get detailed attendance statistics")
        )
    ]
];

// 13. Parent Portal - Homework
$collection['item'][] = [
    "name" => "13. Parent Portal - Homework",
    "description" => "Homework Tracking APIs",
    "item" => [
        array_merge(
            ["name" => "Get Homework List"],
            createAuthRequest("GET", "/guardian/homework?student_id={{student_id}}", null,
                "Get all homework assignments")
        ),
        array_merge(
            ["name" => "Get Homework Stats"],
            createAuthRequest("GET", "/guardian/homework/stats?student_id={{student_id}}", null,
                "Get homework completion statistics")
        ),
        array_merge(
            ["name" => "Get Homework Detail"],
            createAuthRequest("GET", "/guardian/homework/HW001", null,
                "Get detailed information about a homework assignment")
        ),
        array_merge(
            ["name" => "Submit Homework"],
            createAuthRequest("POST", "/guardian/homework/HW001/submit", [
                "student_id" => "{{student_id}}",
                "submission_notes" => "Completed all questions"
            ], "Submit homework on behalf of student")
        ),
        array_merge(
            ["name" => "Update Homework Status"],
            createAuthRequest("PUT", "/guardian/homework/HW001/status", [
                "student_id" => "{{student_id}}",
                "status" => "in_progress"
            ], "Update homework status")
        )
    ]
];

// 14. Parent Portal - Announcements
$collection['item'][] = [
    "name" => "14. Parent Portal - Announcements",
    "description" => "School Announcements APIs",
    "item" => [
        array_merge(
            ["name" => "Get Announcements"],
            createAuthRequest("GET", "/guardian/announcements?student_id={{student_id}}", null,
                "Get all school announcements")
        ),
        array_merge(
            ["name" => "Get Recent Announcements"],
            createAuthRequest("GET", "/guardian/announcements/recent?student_id={{student_id}}", null,
                "Get recent announcements for dashboard")
        ),
        array_merge(
            ["name" => "Get Announcement Detail"],
            createAuthRequest("GET", "/guardian/announcements/ANN001", null,
                "Get detailed information about an announcement")
        ),
        array_merge(
            ["name" => "Mark as Read"],
            createAuthRequest("POST", "/guardian/announcements/ANN001/read", null,
                "Mark announcement as read")
        ),
        array_merge(
            ["name" => "Mark All as Read"],
            createAuthRequest("POST", "/guardian/announcements/mark-all-read", null,
                "Mark all announcements as read")
        )
    ]
];

// 15. Parent Portal - School Information
$collection['item'][] = [
    "name" => "15. Parent Portal - School Info",
    "description" => "School Information APIs",
    "item" => [
        array_merge(
            ["name" => "Get School Information"],
            createAuthRequest("GET", "/guardian/school/info", null,
                "Get general school information")
        ),
        array_merge(
            ["name" => "Get School Rules"],
            createAuthRequest("GET", "/guardian/school/rules", null,
                "Get school rules and regulations")
        ),
        array_merge(
            ["name" => "Get Contact Information"],
            createAuthRequest("GET", "/guardian/school/contact", null,
                "Get school contact information")
        ),
        array_merge(
            ["name" => "Get School Facilities"],
            createAuthRequest("GET", "/guardian/school/facilities", null,
                "Get information about school facilities")
        )
    ]
];

// Save updated collection
file_put_contents($collectionFile, json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "✅ Postman collection updated successfully!\n";
echo "📊 Total folders: " . count($collection['item']) . "\n";

$totalEndpoints = 0;
foreach ($collection['item'] as $folder) {
    $count = count($folder['item']);
    $totalEndpoints += $count;
    echo "   - {$folder['name']}: {$count} endpoints\n";
}

echo "📡 Total endpoints: {$totalEndpoints}\n";
echo "\n";
echo "📁 File: UNIFIED_APP_POSTMAN_COLLECTION.json\n";
echo "🔗 Import this file into Postman to test all APIs\n";

?>
