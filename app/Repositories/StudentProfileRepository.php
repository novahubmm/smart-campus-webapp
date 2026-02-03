<?php

namespace App\Repositories;

use App\DTOs\StudentProfile\StudentProfileStoreData;
use App\DTOs\StudentProfile\StudentProfileUpdateData;
use App\Interfaces\StudentProfileRepositoryInterface;
use App\Models\StudentProfile;
use Illuminate\Support\Str;

class StudentProfileRepository implements StudentProfileRepositoryInterface
{
    public function create(StudentProfileStoreData $data, string $userId): StudentProfile
    {
        $studentId = $data->studentIdentifier ?: $this->generateStudentIdentifier();

        return StudentProfile::create([
            'user_id' => $userId,
            'student_identifier' => $studentId,
            'starting_grade_at_school' => $data->startingGradeAtSchool,
            'current_grade' => $data->currentGrade,
            'current_class' => $data->currentClass,
            'guardian_teacher' => $data->guardianTeacher,
            'assistant_teacher' => $data->assistantTeacher,
            'date_of_joining' => $data->dateOfJoining,
            'gender' => $data->gender,
            'ethnicity' => $data->ethnicity,
            'religious' => $data->religious,
            'nrc' => $data->nrc,
            'dob' => $data->dob,
            'previous_school_name' => $data->previousSchoolName,
            'previous_school_address' => $data->previousSchoolAddress,
            'address' => $data->address,
            'father_name' => $data->fatherName,
            'father_nrc' => $data->fatherNrc,
            'father_phone_no' => $data->fatherPhoneNo,
            'father_occupation' => $data->fatherOccupation,
            'mother_name' => $data->motherName,
            'mother_nrc' => $data->motherNrc,
            'mother_phone_no' => $data->motherPhoneNo,
            'mother_occupation' => $data->motherOccupation,
            'emergency_contact_phone_no' => $data->emergencyContactPhoneNo,
            'in_school_relative_name' => $data->inSchoolRelativeName,
            'in_school_relative_grade' => $data->inSchoolRelativeGrade,
            'in_school_relative_relationship' => $data->inSchoolRelativeRelationship,
            'blood_type' => $data->bloodType,
            'weight' => $data->weight,
            'height' => $data->height,
            'medicine_allergy' => $data->medicineAllergy,
            'food_allergy' => $data->foodAllergy,
            'medical_directory' => $data->medicalDirectory,
            'photo_path' => $data->photoPath,
            'class_id' => $data->classId,
            'grade_id' => $data->gradeId,
            'status' => $data->status,
        ]);
    }

    public function update(StudentProfileUpdateData $data): StudentProfile
    {
        $studentId = $data->studentIdentifier ?: $data->profile->student_identifier ?: $this->generateStudentIdentifier();

        $data->profile->update([
            'user_id' => $data->userId,
            'student_identifier' => $studentId,
            'starting_grade_at_school' => $data->startingGradeAtSchool,
            'current_grade' => $data->currentGrade,
            'current_class' => $data->currentClass,
            'guardian_teacher' => $data->guardianTeacher,
            'assistant_teacher' => $data->assistantTeacher,
            'date_of_joining' => $data->dateOfJoining,
            'gender' => $data->gender,
            'ethnicity' => $data->ethnicity,
            'religious' => $data->religious,
            'nrc' => $data->nrc,
            'dob' => $data->dob,
            'previous_school_name' => $data->previousSchoolName,
            'previous_school_address' => $data->previousSchoolAddress,
            'address' => $data->address,
            'father_name' => $data->fatherName,
            'father_nrc' => $data->fatherNrc,
            'father_phone_no' => $data->fatherPhoneNo,
            'father_occupation' => $data->fatherOccupation,
            'mother_name' => $data->motherName,
            'mother_nrc' => $data->motherNrc,
            'mother_phone_no' => $data->motherPhoneNo,
            'mother_occupation' => $data->motherOccupation,
            'emergency_contact_phone_no' => $data->emergencyContactPhoneNo,
            'in_school_relative_name' => $data->inSchoolRelativeName,
            'in_school_relative_grade' => $data->inSchoolRelativeGrade,
            'in_school_relative_relationship' => $data->inSchoolRelativeRelationship,
            'blood_type' => $data->bloodType,
            'weight' => $data->weight,
            'height' => $data->height,
            'medicine_allergy' => $data->medicineAllergy,
            'food_allergy' => $data->foodAllergy,
            'medical_directory' => $data->medicalDirectory,
            'photo_path' => $data->photoPath,
            'class_id' => $data->classId,
            'grade_id' => $data->gradeId,
            'status' => $data->status,
        ]);

        return $data->profile->refresh();
    }

    private function generateStudentIdentifier(): string
    {
        return 'STD-' . Str::upper(Str::random(6));
    }
}
