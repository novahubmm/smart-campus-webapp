# Curriculum API Documentation

## Overview
The Curriculum API allows teachers to view and track curriculum progress for their subjects and classes through the mobile app.

## Authentication
All endpoints require authentication via Sanctum token:
```
Authorization: Bearer {token}
```

## API Endpoints

### 1. Get Subject Curriculum
**GET** `/api/v1/curriculum/subjects/{subjectId}`

Query: `?grade_id=uuid` (optional)

Response:
```json
{
  "success": true,
  "data": {
    "subject": { "id": "uuid", "name": "Mathematics", "code": "MATH101", "type": "Core" },
    "chapters": [
      {
        "id": "uuid",
        "title": "Introduction to Algebra",
        "order": 1,
        "topics_count": 3,
        "topics": [
          { "id": "uuid", "title": "Variables", "order": 1 },
          { "id": "uuid", "title": "Expressions", "order": 2 }
        ]
      }
    ],
    "total_chapters": 5,
    "total_topics": 25
  }
}
```

### 2. Get Teacher's Subjects
**GET** `/api/v1/curriculum/teacher/subjects`

Returns all subjects assigned to the teacher with curriculum stats.

### 3. Get Teacher's Classes Progress
**GET** `/api/v1/curriculum/teacher/classes`

Returns progress summary for all classes assigned to the teacher.

### 4. Get Class Progress Detail
**GET** `/api/v1/curriculum/classes/{classId}/progress`

Query: `?subject_id=uuid` (optional)

Returns detailed curriculum progress for a class with status per topic.

### 5. Update Topic Progress
**POST** `/api/v1/curriculum/topics/{topicId}/progress`

Request:
```json
{
  "class_id": "uuid",
  "status": "completed",
  "notes": "Students understood well"
}
```

Status values: `not_started`, `in_progress`, `completed`

## Web Management

In Academic Management > Subjects > Subject Detail:
- Click "Edit Curriculum" to open the bulk editor
- Add chapters with their topics in a single form
- Save all at once for efficient input
