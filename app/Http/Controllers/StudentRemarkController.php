<?php

namespace App\Http\Controllers;

use App\Models\StudentRemark;
use App\Models\TeacherProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StudentRemarkController extends Controller
{
    /**
     * Store a new student remark (Web)
     */
    public function store(Request $request)
    {
        Log::info('StudentRemark store called', $request->all());

        $validator = Validator::make($request->all(), [
            'class_id' => 'required|uuid|exists:classes,id',
            'student_id' => 'required|uuid|exists:student_profiles,id',
            'period_id' => 'nullable|uuid|exists:periods,id',
            'date' => 'required|date',
            'remark' => 'required|string|max:1000',
            'type' => 'required|in:note,positive,concern',
        ]);

        if ($validator->fails()) {
            Log::error('StudentRemark validation failed', ['errors' => $validator->errors()->toArray()]);
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = $request->user();
        
        // Try to get teacher profile
        $teacherProfileId = null;
        
        if ($user->teacherProfile) {
            $teacherProfileId = $user->teacherProfile->id;
        } else {
            $teacherProfile = TeacherProfile::where('user_id', $user->id)->first();
            if ($teacherProfile) {
                $teacherProfileId = $teacherProfile->id;
            } else {
                $fallbackTeacher = TeacherProfile::first();
                if ($fallbackTeacher) {
                    $teacherProfileId = $fallbackTeacher->id;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'No teacher profile available. Please create a teacher profile first.',
                    ], 404);
                }
            }
        }

        // If period_id is provided, get the subject from the period
        $subjectId = null;
        if ($request->period_id) {
            $period = \App\Models\Period::find($request->period_id);
            if ($period) {
                $subjectId = $period->subject_id;
            }
        }

        try {
            $remark = StudentRemark::create([
                'class_id' => $request->class_id,
                'student_id' => $request->student_id,
                'subject_id' => $subjectId,
                'period_id' => $request->period_id,
                'teacher_id' => $teacherProfileId,
                'date' => $request->date,
                'remark' => $request->remark,
                'type' => $request->type,
            ]);

            Log::info('StudentRemark created', ['id' => $remark->id]);

            return response()->json([
                'success' => true,
                'message' => __('ongoing_class.Student remark added successfully'),
                'data' => [
                    'id' => $remark->id,
                    'remark' => $remark->remark,
                    'type' => $remark->type,
                    'date' => $remark->date->format('Y-m-d'),
                    'created_at' => $remark->created_at->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('StudentRemark creation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create remark: ' . $e->getMessage(),
            ], 500);
        }
    }
}
