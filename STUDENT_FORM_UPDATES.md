# Student Profile Form Updates

## Summary of Changes

### 1. Database Migration
- Created migration to add: `father_religious`, `father_address`, `mother_religious`, `mother_address`
- Renamed: `current_grade` → `previous_grade`, `current_class` → `previous_class`

### 2. Backend Validation (StudentProfileStoreRequest.php)
- Made `name` always required (not just required_without:user_id)
- Added required validation for:
  - dob
  - previous_grade
  - previous_class
  - previous_school_name
  - previous_school_address
  - father_name, father_nrc, father_religious, father_occupation, father_address
  - mother_name, mother_nrc, mother_religious, mother_occupation, mother_address

### 3. Frontend Validation (JavaScript files)
Updated validation rules for student profile:
- Step 1: ['name']
- Step 2: []
- Step 3: ['dob', 'previous_grade', 'previous_class']
- Step 4: ['previous_school_name', 'previous_school_address']
- Step 5: ['father_name', 'father_nrc', 'father_religious', 'father_occupation', 'father_address', 
           'mother_name', 'mother_nrc', 'mother_religious', 'mother_occupation', 'mother_address']
- Step 6: []

### 4. Form Fields (create.blade.php)
Need to manually update the form HTML to:
- In Step 2: Remove current_grade and current_class fields
- In Step 3: Add dob, previous_grade, previous_class with required asterisks
- In Step 4: Add required asterisks to previous_school_name and previous_school_address
- In Step 5: Add new fields and required asterisks:
  - father_religious (new field with *)
  - father_address (new field with *)
  - mother_religious (new field with *)
  - mother_address (new field with *)
  - Add asterisks to: father_name, father_nrc, father_occupation, mother_name, mother_nrc, mother_occupation

## Next Steps

1. Run the migration:
   ```bash
   php artisan migrate
   ```

2. Update the create.blade.php form HTML (Steps 2-5) to match the new field structure

3. Update the edit.blade.php form similarly

4. Update any DTOs or Services that handle these fields

5. Add translation keys for new fields in language files
