# Guardian Curriculum API

## Overview
The curriculum API allows guardians to view the learning curriculum for their student's subjects, including chapters, topics, and progress tracking.

## Endpoint

### Get Subject Curriculum
**URL:** `GET /api/v1/guardian/students/{student_id}/subjects/{subject_id}/curriculum`

**Authentication:** Required (Bearer Token)

**Parameters:**
- `student_id` (path, required): The student's ID
- `subject_id` (path, required): The subject's ID

## Response Structure

```json
{
  "success": true,
  "message": "Success",
  "data": {
    "subject": {
      "id": "string",
      "name": "string"
    },
    "overall_progress": 0.0,
    "total_chapters": 0,
    "total_topics": 0,
    "completed_topics": 0,
    "in_progress_topics": 0,
    "chapters": [
      {
        "id": "string",
        "title": "string",
        "order": 1,
        "total_topics": 0,
        "completed_topics": 0,
        "in_progress_topics": 0,
        "progress_percentage": 0.0,
        "topics": [
          {
            "id": "string",
            "title": "string",
            "order": 1,
            "status": "not_started|in_progress|completed",
            "started_at": "2026-01-15",
            "completed_at": "2026-01-20"
          }
        ]
      }
    ]
  }
}
```

## Topic Status Values
- `not_started`: Topic has not been started yet
- `in_progress`: Topic is currently being taught
- `completed`: Topic has been completed

## Progress Calculation
- **Overall Progress**: Percentage of all topics completed (in_progress topics count as 50%)
- **Chapter Progress**: Percentage of topics in that chapter completed
- Formula: `((completed + (in_progress * 0.5)) / total) * 100`

## Example Usage

### cURL
```bash
curl -X GET "https://api.example.com/api/v1/guardian/students/{student_id}/subjects/{subject_id}/curriculum" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### JavaScript (Fetch)
```javascript
const response = await fetch(
  `${baseUrl}/guardian/students/${studentId}/subjects/${subjectId}/curriculum`,
  {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  }
);
const data = await response.json();
```

## Use Cases

1. **Curriculum Overview**: Display all chapters and topics for a subject
2. **Progress Tracking**: Show student's learning progress
3. **Parent Engagement**: Help parents understand what their child is learning
4. **Study Planning**: Identify upcoming topics and completed material

## Notes

- Curriculum can be grade-specific or general (applies to all grades)
- Progress is tracked at the class level (all students in the same class share progress)
- Teachers update progress through the teacher portal
- Progress dates are optional and may be null for not_started topics
