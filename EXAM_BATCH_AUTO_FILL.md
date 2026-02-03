# Exam Batch Auto-Fill from Grade

## Overview
When creating or updating an exam, the batch_id is automatically retrieved from the selected grade, without requiring a batch field in the form.

## Implementation

### Controller Changes
**File:** `scp/app/Http/Controllers/ExamController.php`

#### Store Method
```php
public function store(StoreExamRequest $request): RedirectResponse
{
    $validated = $request->validated();
    
    // Get batch_id from the selected grade
    if (isset($validated['grade_id'])) {
        $grade = Grade::find($validated['grade_id']);
        if ($grade && $grade->batch_id) {
            $validated['batch_id'] = $grade->batch_id;
        }
    }
    
    $data = ExamData::from($validated);
    $this->service->create($data);

    return redirect()->route('exams.index')->with('success', __('Exam created successfully.'));
}
```

#### Update Method
```php
public function update(UpdateExamRequest $request, Exam $exam): RedirectResponse
{
    $validated = $request->validated();
    
    // Get batch_id from the selected grade
    if (isset($validated['grade_id'])) {
        $grade = Grade::find($validated['grade_id']);
        if ($grade && $grade->batch_id) {
            $validated['batch_id'] = $grade->batch_id;
        }
    }
    
    $data = ExamData::from($validated);
    $this->service->update($exam, $data);

    return redirect()->route('exams.index')->with('success', __('Exam updated successfully.'));
}
```

## How It Works

### Data Flow
1. User selects a grade in the exam form
2. Form is submitted with grade_id
3. Controller receives the validated data
4. Controller looks up the Grade model by grade_id
5. Controller extracts batch_id from the grade
6. batch_id is added to the validated data
7. ExamData DTO is created with batch_id included
8. Exam is created/updated with the batch_id

### Database Relationship
```
Grade → batch_id (foreign key)
  ↓
Batch
```

Each grade belongs to a batch (academic year), so when an exam is created for a grade, it automatically inherits that grade's batch.

## Benefits

✅ **Simplified Form** - No need for batch dropdown in the UI
✅ **Data Consistency** - Batch always matches the grade's batch
✅ **Automatic** - No manual selection required
✅ **Validation** - Grade must exist and have a batch_id
✅ **Flexible** - Works for both create and update operations

## Edge Cases Handled

1. **Grade not found** - batch_id is not set (will use null or default)
2. **Grade has no batch** - batch_id is not set
3. **Grade_id not provided** - batch_id logic is skipped

## Example Scenario

```
User creates exam:
- Selects "Grade 10" (which belongs to "Academic Year 2024-2025")
- Fills other fields (name, type, dates, etc.)
- Submits form

Backend:
- Finds Grade 10
- Gets batch_id from Grade 10 → "2024-2025"
- Creates exam with batch_id = "2024-2025"

Result:
- Exam is associated with correct academic year automatically
```

## Testing Checklist
- [ ] Create exam with grade → batch_id is set correctly
- [ ] Update exam with different grade → batch_id updates
- [ ] Create exam without grade → batch_id is null (if allowed)
- [ ] Grade with no batch → batch_id is null
- [ ] Exam shows correct batch in detail view
- [ ] Exam filters by batch work correctly
