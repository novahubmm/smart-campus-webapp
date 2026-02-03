<?php

namespace App\Http\Controllers;

use App\DTOs\SalaryPayroll\PayrollFilterData;
use App\DTOs\SalaryPayroll\PaySalaryPayrollData;
use App\Http\Requests\SalaryPayroll\PayPayrollRequest;
use App\Models\StaffProfile;
use App\Models\TeacherProfile;
use App\Services\SalaryPayrollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalaryPayrollController extends Controller
{
    public function __construct(private readonly SalaryPayrollService $service) {}

    public function index(Request $request): View
    {
        $filter = PayrollFilterData::from($request->all());
        $year = $filter->year ?? now()->year;
        $month = $filter->month ?? now()->month;

        $entries = $this->service->getCurrentMonthEntries($year, $month);
        $history = $this->service->getHistory($filter);

        // Calculate stats
        $totalEmployees = $entries->count();
        $paidCount = $entries->filter(fn($e) => $e->payroll?->status === 'paid')->count();
        $pendingCount = $totalEmployees - $paidCount;
        $totalPayout = $entries->sum(fn($e) => $e->payroll?->amount ?? $e->basicSalary);
        $withdrawnAmount = $entries->filter(fn($e) => $e->payroll?->status === 'paid')
            ->sum(fn($e) => $e->payroll->amount);

        $stats = [
            'totalEmployees' => $totalEmployees,
            'paidCount' => $paidCount,
            'pendingCount' => $pendingCount,
            'totalPayout' => $totalPayout,
            'withdrawnAmount' => $withdrawnAmount,
            'teacherCount' => $entries->filter(fn($e) => $e->employeeType === 'teacher')->count(),
            'staffCount' => $entries->filter(fn($e) => $e->employeeType === 'staff')->count(),
        ];

        // Build payroll entries for Alpine.js
        $payrollEntries = $entries->map(fn($entry) => [
            'employee_type' => $entry->employeeType,
            'employee_id' => $entry->employeeId,
            'name' => $entry->name,
            'position' => $entry->position,
            'department' => $entry->department ?? '-',
            'hire_date' => $entry->hireDate,
            'basic_salary' => (float) $entry->basicSalary,
            'payment_method' => $entry->payroll?->payment_method ?? $entry->paymentMethod,
            'working_days' => $entry->payroll?->working_days ?? 21,
            'days_present' => $entry->payroll?->days_present ?? 0,
            'leave_days' => $entry->payroll?->leave_days ?? 0,
            'days_absent' => $entry->payroll?->days_absent ?? 0,
            'attendance_allowance' => (float) ($entry->payroll?->attendance_allowance ?? 0),
            'loyalty_bonus' => (float) ($entry->payroll?->loyalty_bonus ?? 0),
            'other_bonus' => (float) ($entry->payroll?->other_bonus ?? 0),
            'total_salary' => (float) ($entry->payroll?->amount ?? $entry->basicSalary),
            'status' => $entry->payroll?->status ?? 'draft',
            'paid_at' => $entry->payroll?->paid_at?->format('M j, Y H:i'),
            'payroll_id' => $entry->payroll?->id,
        ])->values()->toArray();

        // History entries with employee details
        $historyCollection = $history->getCollection();
        $staffProfiles = StaffProfile::with('user')
            ->whereIn('id', $historyCollection->where('employee_type', 'staff')->pluck('employee_id'))
            ->get()
            ->keyBy('id');
        $teacherProfiles = TeacherProfile::with('user')
            ->whereIn('id', $historyCollection->where('employee_type', 'teacher')->pluck('employee_id'))
            ->get()
            ->keyBy('id');

        $historyEntries = $historyCollection->map(function ($record) use ($staffProfiles, $teacherProfiles) {
            $profile = $record->employee_type === 'teacher'
                ? $teacherProfiles[$record->employee_id] ?? null
                : $staffProfiles[$record->employee_id] ?? null;

            return [
                'record' => $record,
                'employee_name' => $profile?->user?->name ?? __('Employee'),
                'position' => $profile?->position ?? '-',
                'department' => $profile?->department?->name ?? '-',
            ];
        })->toArray();

        // Build history entries for Alpine.js (flatten the data)
        $historyEntriesJson = collect($historyEntries)->map(function ($item) {
            $record = $item['record'];
            return [
                'employee_type' => $record->employee_type,
                'employee_id' => $record->employee_id,
                'employee_name' => $item['employee_name'],
                'position' => $item['position'],
                'department' => $item['department'],
                'working_days' => $record->working_days ?? 21,
                'days_present' => $record->days_present ?? 0,
                'leave_days' => $record->leave_days ?? 0,
                'days_absent' => $record->days_absent ?? 0,
                'basic_salary' => (float) ($record->basic_salary ?? 0),
                'attendance_allowance' => (float) ($record->attendance_allowance ?? 0),
                'loyalty_bonus' => (float) ($record->loyalty_bonus ?? 0),
                'other_bonus' => (float) ($record->other_bonus ?? 0),
                'amount' => (float) ($record->amount ?? 0),
                'payment_method' => $record->payment_method ?? '-',
                'paid_at' => $record->paid_at?->format('M j, Y') ?? '-',
                'status' => $record->status ?? 'draft',
            ];
        })->values()->toArray();

        return view('salary-payroll.index', [
            'entries' => $entries,
            'history' => $history,
            'stats' => $stats,
            'filter' => $filter,
            'selectedYear' => $year,
            'selectedMonth' => $month,
            'payrollEntries' => $payrollEntries,
            'historyEntries' => $historyEntries,
            'historyEntriesJson' => $historyEntriesJson,
        ]);
    }

    public function pay(PayPayrollRequest $request): RedirectResponse|JsonResponse
    {
        $data = PaySalaryPayrollData::from(array_merge($request->validated(), [
            'processed_by' => $request->user()?->id,
        ]));

        $payroll = $this->service->pay($data);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Payroll payment processed successfully.'),
                'payroll' => [
                    'id' => $payroll->id,
                    'employee_type' => $payroll->employee_type,
                    'employee_id' => $payroll->employee_id,
                    'amount' => $payroll->amount,
                    'status' => $payroll->status,
                    'paid_at' => $payroll->paid_at?->format('M j, Y H:i'),
                    'payment_method' => $payroll->payment_method,
                    'remark' => $payroll->remark,
                ],
            ]);
        }

        return back()->with('success', __('Payroll recorded successfully.'));
    }
}
