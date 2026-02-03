# Exam Results API Documentation

## Endpoint 1: GET `/api/v1/teacher/exams/{exam_id}/results`

This endpoint returns detailed exam results including student-wise remarks for each subject.

### Authentication
- Requires Bearer token authentication
- User must have teacher profile

### Response Structure

```json
{
    "success": true,
    "data": {
        "id": "exam-uuid",
        "title": "Monthly Test - Kindergarten A",
        "type": "monthly (100 marks, 6 subjects)",
        "class": "Kindergarten A",
        "grade": "Kindergarten",
        "date": "2026-01-12",
        "time": "12:00 AM - 12:00 AM",
        "location": "Room 101",
        "status": "completed",
        "subjects": [
            {
                "id": "subject-uuid",
                "name": "Myanmar",
                "marks": 100,
                "is_your_subject": true,
                "pass_percentage": 60
            }
        ],
        "students": [
            {
                "id": "student-uuid",
                "name": "Student Name",
                "student_id": "KG-A-001",
                "avatar": "url-to-avatar-or-null",
                "total_marks": 600,
                "obtained_marks": 520,
                "percentage": 86.67,
                "overall_grade": "A",
                "overall_remark": "Excellent performance across all subjects.",
                "subject_results": [
                    {
                        "subject": "Myanmar",
                        "marks_obtained": 95,
                        "total_marks": 100,
                        "grade": "A+",
                        "remark": "Excellent reading and writing skills"
                    }
                ]
            }
        ],
        "total_students": 2
    }
}
```

---

## Endpoint 2: GET `/api/v1/teacher/exams/{exam_id}/results/detailed`

This endpoint returns detailed student-wise results for a specific subject with individual remarks and rankings.

### Authentication
- Requires Bearer token authentication
- User must have teacher profile

### Query Parameters
- `subject_id` (optional): Filter results by specific subject ID

### Response Structure

```json
{
    "success": true,
    "data": {
        "exam": {
            "id": "exam-uuid",
            "title": "Math Quiz 2",
            "grade": "Grade 8B",
            "date": "2025-11-20",
            "time": "02:00 PM - 03:00 PM",
            "location": "Room 205",
            "max_marks": 25
        },
        "statistics": {
            "total_students": 30,
            "pass_count": 25,
            "fail_count": 5
        },
        "students": [
            {
                "id": "67068ff4-a9ac-41ec-bc9e-8a1fd8447ac4",
                "name": "Hay Nwe",
                "student_id": "STU-81536",
                "avatar": null,
                "score": "98.00",
                "grade": "A+",
                "status": "pass",
                "rank": 1,
                "remark": "Excellent work! Shows strong understanding of mathematical concepts."
            }
        ]
    }
}
```

### Key Features

1. **Student-wise Results**: Each student's performance across all subjects
2. **Individual Subject Remarks**: Teacher's comments for each subject
3. **Overall Performance**: Total marks, percentage, and overall grade
4. **Automatic Remarks**: If no specific remarks are provided, the system generates appropriate comments based on performance
5. **Subject Pass Rates**: Shows pass percentage for each subject
6. **Detailed Rankings**: Student rankings with individual subject performance
7. **Statistics**: Pass/fail counts and performance metrics

### Remark Generation Logic

- **90%+ (A+)**: "Excellent performance across all subjects."
- **80-89% (A)**: "Very good performance. Keep up the good work."
- **70-79% (B+)**: "Good performance. Room for improvement in some areas."
- **60-69% (B)**: "Satisfactory performance. Needs more focus on studies."
- **40-59% (C)**: "Below average performance. Requires additional support."
- **<40% (F)**: "Poor performance. Immediate attention and support needed."

### Usage Examples

#### Get Overall Exam Results
```bash
curl -X GET "http://your-domain.com/api/v1/teacher/exams/019bb405-285d-7283-8e6c-af8f955ebb10/results" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

#### Get Detailed Subject Results
```bash
curl -X GET "http://your-domain.com/api/v1/teacher/exams/019bb405-285d-7283-8e6c-af8f955ebb10/results/detailed?subject_id=subject-uuid" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

### Error Responses

- **401**: Unauthenticated or teacher profile not found
- **404**: Exam not found or no results found for this exam and subject
- **500**: Server error

These endpoints provide comprehensive exam results that can be used for:
- Generating report cards
- Parent-teacher conferences
- Academic performance tracking
- Student progress monitoring
- Subject-specific performance analysis