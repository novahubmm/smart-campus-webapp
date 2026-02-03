<?php

namespace App\Http\Controllers;

use App\Models\ClassRemark;
use App\Models\TeacherProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClassRemarkController extends Controller
{
    /**
     * Store a new class remark (Web)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|uuid|exists:classes,id',
            'subject_id' => 'nullable|uuid|exists:subjects,id',
            'period_id' => 'nullable|uuid|exists:periods,id',
            'date' => 'required|date',
            'remark' => 'required|string|max:1000',
            'type' => 'required|in:note,positive,concern',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = $request->user();
        
        // Try to get teacher profile - check teacherProfile relationship first
        $teacherProfileId = null;
        
        if ($user->teacherProfile) {
            $teacherProfileId = $user->teacherProfile->id;
        } else {
            // For admin/staff users, try to find a teacher profile by user_id
            $teacherProfile = TeacherProfile::where('user_id', $user->id)->first();
            if ($teacherProfile) {
                $teacherProfileId = $teacherProfile->id;
            } else {
                // If still no teacher profile, get the first available teacher as fallback
                // This allows admins to add remarks
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
        $subjectId = $request->subject_id;
        if ($request->period_id && !$subjectId) {
            $period = \App\Models\Period::find($request->period_id);
            if ($period) {
                $subjectId = $period->subject_id;
            }
        }

        $remark = ClassRemark::create([
            'class_id' => $request->class_id,
            'subject_id' => $subjectId,
            'period_id' => $request->period_id,
            'teacher_id' => $teacherProfileId,
            'date' => $request->date,
            'remark' => $request->remark,
            'type' => $request->type,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('ongoing_class.Class remark added successfully'),
            'data' => [
                'id' => $remark->id,
                'remark' => $remark->remark,
                'type' => $remark->type,
                'date' => $remark->date->format('Y-m-d'),
                'created_at' => $remark->created_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
