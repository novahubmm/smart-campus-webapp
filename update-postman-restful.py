#!/usr/bin/env python3
"""
Script to add RESTful endpoints to Postman collection
Adds new RESTful URLs alongside existing query parameter URLs
"""

import json
import sys

def create_restful_endpoint(name, method, path_parts, query_params=None, body=None, description=""):
    """Create a RESTful endpoint structure"""
    endpoint = {
        "name": f"{name} (RESTful)",
        "request": {
            "method": method,
            "header": [
                {
                    "key": "Accept",
                    "value": "application/json"
                }
            ],
            "url": {
                "raw": f"{{{{base_url}}}}/{'/'.join(path_parts)}",
                "host": ["{{base_url}}"],
                "path": path_parts
            }
        }
    }
    
    if method in ["POST", "PUT", "PATCH"]:
        endpoint["request"]["header"].append({
            "key": "Content-Type",
            "value": "application/json"
        })
    
    if query_params:
        query_list = []
        for key, value in query_params.items():
            query_list.append({
                "key": key,
                "value": value
            })
        endpoint["request"]["url"]["query"] = query_list
        # Update raw URL with query params
        query_str = "&".join([f"{k}={v}" for k, v in query_params.items()])
        endpoint["request"]["url"]["raw"] += f"?{query_str}"
    
    if body:
        endpoint["request"]["body"] = {
            "mode": "raw",
            "raw": json.dumps(body, indent=4)
        }
    
    if description:
        endpoint["request"]["description"] = description
    
    return endpoint

def main():
    # Read the existing collection
    with open('UNIFIED_APP_POSTMAN_COLLECTION.json', 'r') as f:
        collection = json.load(f)
    
    # Find Guardian Specific section
    guardian_section = None
    for item in collection['item']:
        if item.get('name') == 'Guardian Specific':
            guardian_section = item
            break
    
    if not guardian_section:
        print("Guardian Specific section not found!")
        return
    
    # Add student_id variable if not exists
    student_id_var = {
        "key": "student_id",
        "value": "1",
        "type": "string"
    }
    
    if not any(v.get('key') == 'student_id' for v in collection.get('variable', [])):
        collection['variable'].append(student_id_var)
    
    # Create new RESTful endpoints folder
    restful_folder = {
        "name": "RESTful Endpoints (NEW)",
        "description": "New RESTful URL structure with student_id in path. Use these for new development.",
        "item": []
    }
    
    # 1. Attendance endpoints
    attendance_folder = {
        "name": "Attendance",
        "item": [
            create_restful_endpoint(
                "Get Attendance Records",
                "GET",
                ["guardian", "students", "{{student_id}}", "attendance"],
                {"month": "2", "year": "2026"},
                description="Get student attendance records for a specific month"
            ),
            create_restful_endpoint(
                "Get Attendance Summary",
                "GET",
                ["guardian", "students", "{{student_id}}", "attendance", "summary"],
                {"month": "2", "year": "2026"},
                description="Get attendance summary with statistics"
            ),
            create_restful_endpoint(
                "Get Attendance Calendar",
                "GET",
                ["guardian", "students", "{{student_id}}", "attendance", "calendar"],
                {"month": "2", "year": "2026"},
                description="Get attendance calendar view"
            ),
            create_restful_endpoint(
                "Get Attendance Stats",
                "GET",
                ["guardian", "students", "{{student_id}}", "attendance", "stats"],
                description="Get overall attendance statistics"
            )
        ]
    }
    
    # 2. Exam endpoints
    exam_folder = {
        "name": "Exams",
        "item": [
            create_restful_endpoint(
                "Get Exams List",
                "GET",
                ["guardian", "students", "{{student_id}}", "exams"],
                {"subject_id": ""},
                description="Get all exams for student"
            ),
            create_restful_endpoint(
                "Get Upcoming Exams",
                "GET",
                ["guardian", "students", "{{student_id}}", "exams", "upcoming"],
                description="Get upcoming exams with countdown"
            ),
            create_restful_endpoint(
                "Get Past Exams",
                "GET",
                ["guardian", "students", "{{student_id}}", "exams", "past"],
                {"limit": "10"},
                description="Get past exams with results"
            ),
            create_restful_endpoint(
                "Get Performance Trends",
                "GET",
                ["guardian", "students", "{{student_id}}", "exams", "performance-trends"],
                {"subject_id": ""},
                description="Get exam performance trends with analysis"
            ),
            create_restful_endpoint(
                "Compare Exams",
                "POST",
                ["guardian", "students", "{{student_id}}", "exams", "compare"],
                body={"exam_ids": ["1", "2", "3"]},
                description="Compare multiple exams side-by-side"
            ),
            create_restful_endpoint(
                "Get Exam Results",
                "GET",
                ["guardian", "students", "{{student_id}}", "exams", "1", "results"],
                description="Get detailed results for a specific exam"
            )
        ]
    }
    
    # 3. Homework endpoints
    homework_folder = {
        "name": "Homework",
        "item": [
            create_restful_endpoint(
                "Get Homework List",
                "GET",
                ["guardian", "students", "{{student_id}}", "homework"],
                {"status": "pending", "subject_id": ""},
                description="Get homework list with filters"
            ),
            create_restful_endpoint(
                "Get Homework Detail",
                "GET",
                ["guardian", "students", "{{student_id}}", "homework", "1"],
                description="Get detailed homework information"
            ),
            create_restful_endpoint(
                "Get Homework Stats",
                "GET",
                ["guardian", "students", "{{student_id}}", "homework", "stats"],
                description="Get homework statistics"
            ),
            create_restful_endpoint(
                "Submit Homework",
                "POST",
                ["guardian", "students", "{{student_id}}", "homework", "1", "submit"],
                body={"notes": "Homework completed", "photos": []},
                description="Submit homework with notes and photos"
            ),
            create_restful_endpoint(
                "Update Homework Status",
                "PUT",
                ["guardian", "students", "{{student_id}}", "homework", "1", "status"],
                body={"status": "completed"},
                description="Update homework completion status"
            )
        ]
    }
    
    # 4. Timetable endpoints
    timetable_folder = {
        "name": "Timetable",
        "item": [
            create_restful_endpoint(
                "Get Full Timetable",
                "GET",
                ["guardian", "students", "{{student_id}}", "timetable"],
                description="Get complete weekly timetable"
            ),
            create_restful_endpoint(
                "Get Day Timetable",
                "GET",
                ["guardian", "students", "{{student_id}}", "timetable", "day"],
                {"day": "Monday"},
                description="Get timetable for specific day"
            ),
            create_restful_endpoint(
                "Get Class Info",
                "GET",
                ["guardian", "students", "{{student_id}}", "class-info"],
                description="Get basic class information"
            ),
            create_restful_endpoint(
                "Get Detailed Class Info",
                "GET",
                ["guardian", "students", "{{student_id}}", "class-details"],
                description="Get detailed class information with students and teachers"
            ),
            create_restful_endpoint(
                "Get Class Teachers",
                "GET",
                ["guardian", "students", "{{student_id}}", "class-teachers"],
                description="Get list of class teachers"
            ),
            create_restful_endpoint(
                "Get Class Statistics",
                "GET",
                ["guardian", "students", "{{student_id}}", "class-statistics"],
                description="Get class performance statistics"
            )
        ]
    }
    
    # 5. Fees endpoints
    fees_folder = {
        "name": "Fees",
        "item": [
            create_restful_endpoint(
                "Get All Fees",
                "GET",
                ["guardian", "students", "{{student_id}}", "fees"],
                {"status": "", "per_page": "10"},
                description="Get all fee records with pagination"
            ),
            create_restful_endpoint(
                "Get Pending Fee",
                "GET",
                ["guardian", "students", "{{student_id}}", "fees", "pending"],
                description="Get current pending fee"
            ),
            create_restful_endpoint(
                "Get Fee Details",
                "GET",
                ["guardian", "students", "{{student_id}}", "fees", "1"],
                description="Get detailed fee information"
            ),
            create_restful_endpoint(
                "Initiate Payment",
                "POST",
                ["guardian", "students", "{{student_id}}", "fees", "1", "payment"],
                body={"payment_method": "easy_pay", "amount": 50000},
                description="Initiate fee payment"
            ),
            create_restful_endpoint(
                "Get Payment History",
                "GET",
                ["guardian", "students", "{{student_id}}", "fees", "payment-history"],
                {"status": "", "per_page": "10"},
                description="Get payment history with pagination"
            ),
            create_restful_endpoint(
                "Get Payment Receipt",
                "GET",
                ["guardian", "students", "{{student_id}}", "fees", "receipts", "1"],
                description="Get payment receipt details"
            ),
            create_restful_endpoint(
                "Download Receipt",
                "GET",
                ["guardian", "students", "{{student_id}}", "fees", "receipts", "1", "download"],
                description="Get receipt download URL"
            ),
            create_restful_endpoint(
                "Get Payment Summary",
                "GET",
                ["guardian", "students", "{{student_id}}", "fees", "summary"],
                {"year": "2026"},
                description="Get payment summary for year"
            )
        ]
    }
    
    # 6. Leave Request endpoints
    leave_folder = {
        "name": "Leave Requests",
        "item": [
            create_restful_endpoint(
                "Get Leave Requests",
                "GET",
                ["guardian", "students", "{{student_id}}", "leave-requests"],
                {"status": "pending"},
                description="Get leave requests with status filter"
            ),
            create_restful_endpoint(
                "Get Leave Request Detail",
                "GET",
                ["guardian", "students", "{{student_id}}", "leave-requests", "1"],
                description="Get detailed leave request information"
            ),
            create_restful_endpoint(
                "Create Leave Request",
                "POST",
                ["guardian", "students", "{{student_id}}", "leave-requests"],
                body={
                    "leave_type": "sick",
                    "start_date": "2026-02-10",
                    "end_date": "2026-02-11",
                    "reason": "Student has fever"
                },
                description="Submit new leave request"
            ),
            create_restful_endpoint(
                "Update Leave Request",
                "PUT",
                ["guardian", "students", "{{student_id}}", "leave-requests", "1"],
                body={
                    "reason": "Updated reason"
                },
                description="Update existing leave request"
            ),
            create_restful_endpoint(
                "Delete Leave Request",
                "DELETE",
                ["guardian", "students", "{{student_id}}", "leave-requests", "1"],
                description="Delete leave request"
            ),
            create_restful_endpoint(
                "Get Leave Stats",
                "GET",
                ["guardian", "students", "{{student_id}}", "leave-requests", "stats"],
                description="Get leave request statistics"
            )
        ]
    }
    
    # 7. Announcements endpoints
    announcements_folder = {
        "name": "Announcements",
        "item": [
            create_restful_endpoint(
                "Get Announcements",
                "GET",
                ["guardian", "students", "{{student_id}}", "announcements"],
                {"category": "", "is_read": ""},
                description="Get announcements with filters"
            ),
            create_restful_endpoint(
                "Get Announcement Detail",
                "GET",
                ["guardian", "students", "{{student_id}}", "announcements", "1"],
                description="Get detailed announcement"
            ),
            create_restful_endpoint(
                "Mark as Read",
                "POST",
                ["guardian", "students", "{{student_id}}", "announcements", "1", "read"],
                description="Mark announcement as read"
            ),
            create_restful_endpoint(
                "Mark All as Read",
                "POST",
                ["guardian", "students", "{{student_id}}", "announcements", "mark-all-read"],
                description="Mark all announcements as read"
            )
        ]
    }
    
    # 8. Curriculum endpoints
    curriculum_folder = {
        "name": "Curriculum",
        "item": [
            create_restful_endpoint(
                "Get Curriculum Overview",
                "GET",
                ["guardian", "students", "{{student_id}}", "curriculum"],
                description="Get curriculum overview"
            ),
            create_restful_endpoint(
                "Get Subject Curriculum",
                "GET",
                ["guardian", "students", "{{student_id}}", "curriculum", "subjects", "1"],
                description="Get curriculum for specific subject"
            ),
            create_restful_endpoint(
                "Get Chapters",
                "GET",
                ["guardian", "students", "{{student_id}}", "curriculum", "chapters"],
                {"subject_id": "1"},
                description="Get chapters for subject"
            ),
            create_restful_endpoint(
                "Get Chapter Detail",
                "GET",
                ["guardian", "students", "{{student_id}}", "curriculum", "chapters", "1"],
                description="Get detailed chapter information"
            )
        ]
    }
    
    # 9. Report Cards endpoints
    report_cards_folder = {
        "name": "Report Cards",
        "item": [
            create_restful_endpoint(
                "Get Report Cards",
                "GET",
                ["guardian", "students", "{{student_id}}", "report-cards"],
                description="Get all report cards"
            ),
            create_restful_endpoint(
                "Get Report Card Detail",
                "GET",
                ["guardian", "students", "{{student_id}}", "report-cards", "1"],
                description="Get detailed report card"
            )
        ]
    }
    
    # 10. Dashboard endpoints
    dashboard_folder = {
        "name": "Dashboard",
        "item": [
            create_restful_endpoint(
                "Get Dashboard",
                "GET",
                ["guardian", "students", "{{student_id}}", "dashboard"],
                description="Get complete dashboard data"
            ),
            create_restful_endpoint(
                "Get Today's Schedule",
                "GET",
                ["guardian", "students", "{{student_id}}", "today-schedule"],
                description="Get today's class schedule"
            ),
            create_restful_endpoint(
                "Get Upcoming Homework",
                "GET",
                ["guardian", "students", "{{student_id}}", "upcoming-homework"],
                {"limit": "5"},
                description="Get upcoming homework"
            ),
            create_restful_endpoint(
                "Get Recent Announcements",
                "GET",
                ["guardian", "students", "{{student_id}}", "announcements", "recent"],
                {"limit": "5"},
                description="Get recent announcements"
            ),
            create_restful_endpoint(
                "Get Fee Reminder",
                "GET",
                ["guardian", "students", "{{student_id}}", "fee-reminder"],
                description="Get fee payment reminder"
            ),
            create_restful_endpoint(
                "Get Current Class",
                "GET",
                ["guardian", "students", "{{student_id}}", "dashboard", "current-class"],
                description="Get currently active class"
            )
        ]
    }
    
    # 11. Subjects endpoints
    subjects_folder = {
        "name": "Subjects",
        "item": [
            create_restful_endpoint(
                "Get Subjects List",
                "GET",
                ["guardian", "students", "{{student_id}}", "subjects"],
                description="Get all subjects for student"
            ),
            create_restful_endpoint(
                "Get Subject Detail",
                "GET",
                ["guardian", "students", "{{student_id}}", "subjects", "1"],
                description="Get detailed subject information"
            ),
            create_restful_endpoint(
                "Get Subject Performance",
                "GET",
                ["guardian", "students", "{{student_id}}", "subjects", "1", "performance"],
                description="Get performance analysis for subject"
            ),
            create_restful_endpoint(
                "Get Subject Schedule",
                "GET",
                ["guardian", "students", "{{student_id}}", "subjects", "1", "schedule"],
                description="Get class schedule for subject"
            )
        ]
    }
    
    # Add all folders to RESTful folder
    restful_folder["item"] = [
        attendance_folder,
        exam_folder,
        homework_folder,
        timetable_folder,
        fees_folder,
        leave_folder,
        announcements_folder,
        curriculum_folder,
        report_cards_folder,
        dashboard_folder,
        subjects_folder
    ]
    
    # Insert RESTful folder at the beginning of Guardian Specific section
    guardian_section['item'].insert(0, restful_folder)
    
    # Add deprecation notice to existing endpoints
    for item in guardian_section['item']:
        if item.get('name') != 'RESTful Endpoints (NEW)' and 'request' in item:
            if 'description' in item['request']:
                item['request']['description'] = f"⚠️ DEPRECATED - Use RESTful endpoint instead. {item['request'].get('description', '')}"
            else:
                item['request']['description'] = "⚠️ DEPRECATED - Use RESTful endpoint instead"
    
    # Update collection version
    collection['info']['version'] = '2.0.0'
    collection['info']['description'] = 'Complete API collection for Unified Teacher-Guardian Mobile App with RESTful URLs'
    
    # Write updated collection
    with open('UNIFIED_APP_POSTMAN_COLLECTION.json', 'w') as f:
        json.dump(collection, f, indent=4)
    
    print("✅ Postman collection updated successfully!")
    print(f"✅ Added {len(restful_folder['item'])} RESTful endpoint folders")
    print("✅ Added student_id variable")
    print("✅ Marked old endpoints as deprecated")
    print("✅ Updated collection version to 2.0.0")

if __name__ == '__main__':
    try:
        main()
    except Exception as e:
        print(f"❌ Error: {e}")
        sys.exit(1)
