<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayslipController extends Controller
{
    /**
     * Get teacher's payslips
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $teacherProfile = $user->teacherProfile;

        if (!$teacherProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher profile not found',
            ], 404);
        }

        $year = $request->input('year');
        $perPage = $request->input('per_page', 12);

        $query = Payroll::where('employee_type', 'teacher')
            ->where('employee_id', $teacherProfile->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc');

        if ($year) {
            $query->where('year', $year);
        }

        $payrolls = $query->paginate($perPage);

        $payslips = $payrolls->map(function ($payroll) {
            $monthName = Carbon::create($payroll->year, $payroll->month, 1)->format('F');
            
            return [
                'id' => $payroll->id,
                'month' => $monthName,
                'year' => $payroll->year,
                'period' => $monthName . ' ' . $payroll->year,
                'basic_salary' => (int) $payroll->basic_salary,
                'attendance_allowance' => (int) $payroll->attendance_allowance,
                'loyalty_bonus' => (int) $payroll->loyalty_bonus,
                'other_bonus' => (int) $payroll->other_bonus,
                'total_amount' => (int) $payroll->amount,
                'status' => $payroll->status,
                'paid_at' => $payroll->paid_at?->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'payslips' => $payslips,
                'pagination' => [
                    'current_page' => $payrolls->currentPage(),
                    'total_pages' => $payrolls->lastPage(),
                    'total_items' => $payrolls->total(),
                    'per_page' => $payrolls->perPage(),
                ],
            ],
        ]);
    }

    /**
     * Show a specific payslip
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $teacherProfile = $user->teacherProfile;

        if (!$teacherProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher profile not found',
            ], 404);
        }

        $payroll = Payroll::where('id', $id)
            ->where('employee_type', 'teacher')
            ->where('employee_id', $teacherProfile->id)
            ->first();

        if (!$payroll) {
            return response()->json([
                'success' => false,
                'message' => 'Payslip not found',
            ], 404);
        }

        $monthName = Carbon::create($payroll->year, $payroll->month, 1)->format('F');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $payroll->id,
                'month' => $monthName,
                'year' => $payroll->year,
                'period' => $monthName . ' ' . $payroll->year,
                'employee' => [
                    'name' => $user->name,
                    'employee_id' => $teacherProfile->employee_id,
                    'department' => $teacherProfile->department?->name ?? 'Teaching Department',
                    'position' => $teacherProfile->position ?? 'Teacher',
                ],
                'attendance' => [
                    'working_days' => $payroll->working_days,
                    'days_present' => $payroll->days_present,
                    'leave_days' => $payroll->leave_days,
                    'annual_leave' => $payroll->annual_leave ?? 0,
                    'days_absent' => $payroll->days_absent,
                ],
                'earnings' => [
                    'basic_salary' => (int) $payroll->basic_salary,
                    'attendance_allowance' => (int) $payroll->attendance_allowance,
                    'loyalty_bonus' => (int) $payroll->loyalty_bonus,
                    'other_bonus' => (int) $payroll->other_bonus,
                ],
                'total_amount' => (int) $payroll->amount,
                'status' => $payroll->status,
                'payment_info' => [
                    'payment_method' => $payroll->payment_method,
                    'reference' => $payroll->reference,
                    'paid_at' => $payroll->paid_at?->format('Y-m-d H:i:s'),
                    'receptionist_name' => $payroll->receptionist_name,
                ],
                'remark' => $payroll->remark,
                'notes' => $payroll->notes,
            ],
        ]);
    }
}
