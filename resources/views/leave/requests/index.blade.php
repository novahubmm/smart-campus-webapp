<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-violet-600 text-white shadow-lg">
                <i class="fas fa-calendar-times"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('leave.Leave Requests') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('leave.View Leave Requests') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden" x-data="leaveRequestsPage(@js([
        'routes' => [
            'staffPending' => route('leave-requests.staff.pending'),
            'staffHistory' => route('leave-requests.staff.history'),
            'studentPending' => route('leave-requests.students.pending'),
            'studentHistory' => route('leave-requests.students.history'),
            'approve' => '/leave-requests/{id}/approve',
            'reject' => '/leave-requests/{id}/reject',
        ],
        'classes' => $classes,
        'today' => $today,
        'csrf' => csrf_token(),
        'initialTab' => $initialTab,
    ]))" x-init="initPage()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- View Toggle Tabs -->
            <x-academic-tabs :tabs="[
                'staff' => __('leave.Staff / Teacher Leaves'),
                'student' => __('leave.Student Leaves'),
            ]" activeTab="tab" />

            <!-- Staff / Teacher Tab -->
            <div x-show="tab === 'staff'" x-cloak class="space-y-6">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('leave.Pending Requests') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('leave.Staff and teachers awaiting approval') }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <select x-model="staffRole" @change="loadStaffPending" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm">
                                <option value="">{{ __('leave.All Roles') }}</option>
                                <option value="teacher">{{ __('leave.Teacher') }}</option>
                                <option value="staff">{{ __('leave.Staff') }}</option>
                            </select>
                            <input type="search" x-model.debounce.400ms="staffSearch" @input="loadStaffPending" placeholder="{{ __('leave.Search name…') }}" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm" />
                        </div>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Reference') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Requester') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Role') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Department') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Submitted') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Type') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.From') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.To') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Days') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Status') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Reason') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700" x-show="staffPending.length">
                                <template x-for="row in staffPending" :key="row.id">
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white" x-text="row.reference"></td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white" x-text="row.name"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.role"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.department"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatDate(row.submitted_at)"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.leave_type"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatDate(row.start_date)"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatDate(row.end_date)"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.total_days"></td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold" :class="statusClass(row.status)" x-text="titleCase(row.status)"></span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 max-w-xs truncate" x-text="row.reason || '—'" :title="row.reason"></td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button type="button" @click="openViewModal(row)" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-100 text-xs font-semibold hover:bg-blue-100 dark:hover:bg-blue-900/50">
                                                    <i class="fas fa-eye mr-1"></i>{{ __('leave.View') }}
                                                </button>
                                                <button type="button" @click="openApproveModal(row)" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-100 text-xs font-semibold hover:bg-green-100 dark:hover:bg-green-900/50">
                                                    <i class="fas fa-check mr-1"></i>{{ __('leave.Approve') }}
                                                </button>
                                                <button type="button" @click="openRejectModal(row)" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-100 text-xs font-semibold hover:bg-red-100 dark:hover:bg-red-900/50">
                                                    <i class="fas fa-times mr-1"></i>{{ __('leave.Reject') }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tbody x-show="!staffPending.length">
                                <tr>
                                    <td colspan="12" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('leave.No pending requests found.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('leave.History') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('leave.Approved and rejected staff/teacher requests') }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <input type="date" x-model="staffHistoryDate" @change="loadStaffHistory" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm" />
                            <select x-model="staffHistoryStatus" @change="loadStaffHistory" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm">
                                <option value="">{{ __('leave.All Status') }}</option>
                                <option value="approved">{{ __('leave.Approved') }}</option>
                                <option value="rejected">{{ __('leave.Rejected') }}</option>
                            </select>
                            <select x-model="staffHistoryRole" @change="loadStaffHistory" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm">
                                <option value="">{{ __('leave.All Roles') }}</option>
                                <option value="teacher">{{ __('leave.Teacher') }}</option>
                                <option value="staff">{{ __('leave.Staff') }}</option>
                            </select>
                            <input type="search" x-model.debounce.400ms="staffHistorySearch" @input="loadStaffHistory" placeholder="{{ __('leave.Search name…') }}" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm" />
                        </div>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Reference') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Requester') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Role') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Department') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Submitted') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Type') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.From') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.To') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Days') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Status') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Reason') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Approved By') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700" x-show="staffHistory.length">
                                <template x-for="row in staffHistory" :key="row.id">
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white" x-text="row.reference"></td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white" x-text="row.name"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.role"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.department"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatDate(row.submitted_at)"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.leave_type"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatDate(row.start_date)"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatDate(row.end_date)"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.total_days"></td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold" :class="statusClass(row.status)" x-text="titleCase(row.status)"></span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 max-w-xs truncate" x-text="row.reason || '—'" :title="row.reason"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                            <div x-text="row.approved_by || '—'"></div>
                                            <div class="text-xs text-gray-500" x-text="row.approved_at ? formatDate(row.approved_at) : ''"></div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tbody x-show="!staffHistory.length">
                                <tr>
                                    <td colspan="13" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('leave.No history found for the selected filters.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Student Tab -->
            <div x-show="tab === 'student'" x-cloak class="space-y-6">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('leave.Pending Requests') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('leave.Students awaiting approval') }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <select x-model="studentClass" @change="loadStudentPending" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm">
                                <option value="">{{ __('leave.All Classes') }}</option>
                                <template x-for="cls in classes" :key="cls.id">
                                    <option :value="cls.id" x-text="classLabel(cls)"></option>
                                </template>
                            </select>
                            <input type="search" x-model.debounce.400ms="studentSearch" @input="loadStudentPending" placeholder="{{ __('leave.Search name…') }}" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm" />
                        </div>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Reference') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Student') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Class') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Submitted') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Type') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.From') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.To') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Days') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Status') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Reason') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700" x-show="studentPending.length">
                                <template x-for="row in studentPending" :key="row.id">
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white" x-text="row.reference"></td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white" x-text="row.name"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.class"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatDate(row.submitted_at)"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.leave_type"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatDate(row.start_date)"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatDate(row.end_date)"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.total_days"></td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold" :class="statusClass(row.status)" x-text="titleCase(row.status)"></span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 max-w-xs truncate" x-text="row.reason || '—'" :title="row.reason"></td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button type="button" @click="openViewModal(row)" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-100 text-xs font-semibold hover:bg-blue-100 dark:hover:bg-blue-900/50">
                                                    <i class="fas fa-eye mr-1"></i>{{ __('leave.View') }}
                                                </button>
                                                <button type="button" @click="openApproveModal(row)" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-100 text-xs font-semibold hover:bg-green-100 dark:hover:bg-green-900/50">
                                                    <i class="fas fa-check mr-1"></i>{{ __('leave.Approve') }}
                                                </button>
                                                <button type="button" @click="openRejectModal(row)" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-100 text-xs font-semibold hover:bg-red-100 dark:hover:bg-red-900/50">
                                                    <i class="fas fa-times mr-1"></i>{{ __('leave.Reject') }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tbody x-show="!studentPending.length">
                                <tr>
                                    <td colspan="12" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('leave.No pending requests found.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('leave.History') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('leave.Approved and rejected student requests') }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <input type="date" x-model="studentHistoryDate" @change="loadStudentHistory" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm" />
                            <select x-model="studentHistoryStatus" @change="loadStudentHistory" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm">
                                <option value="">{{ __('leave.All Status') }}</option>
                                <option value="approved">{{ __('leave.Approved') }}</option>
                                <option value="rejected">{{ __('leave.Rejected') }}</option>
                            </select>
                            <select x-model="studentHistoryClass" @change="loadStudentHistory" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm">
                                <option value="">{{ __('leave.All Classes') }}</option>
                                <template x-for="cls in classes" :key="cls.id">
                                    <option :value="cls.id" x-text="classLabel(cls)"></option>
                                </template>
                            </select>
                            <input type="search" x-model.debounce.400ms="studentHistorySearch" @input="loadStudentHistory" placeholder="{{ __('leave.Search name…') }}" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm" />
                        </div>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Reference') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Student') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Class') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Submitted') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Type') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.From') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.To') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Days') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Status') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Reason') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Approved By') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700" x-show="studentHistory.length">
                                <template x-for="row in studentHistory" :key="row.id">
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white" x-text="row.reference"></td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white" x-text="row.name"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.class"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatDate(row.submitted_at)"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.leave_type"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatDate(row.start_date)"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatDate(row.end_date)"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.total_days"></td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold" :class="statusClass(row.status)" x-text="titleCase(row.status)"></span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 max-w-xs truncate" x-text="row.reason || '—'" :title="row.reason"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                            <div x-text="row.approved_by || '—'"></div>
                                            <div class="text-xs text-gray-500" x-text="row.approved_at ? formatDate(row.approved_at) : ''"></div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tbody x-show="!studentHistory.length">
                                <tr>
                                    <td colspan="12" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('leave.No history found for the selected filters.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approve Modal -->
        <div x-show="showApproveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showApproveModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 transition-opacity" @click="showApproveModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showApproveModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-check text-green-600 dark:text-green-400"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">{{ __('leave.Approve Leave Request') }}</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('leave.You are about to approve the leave request for') }} <span class="font-semibold text-gray-900 dark:text-white" x-text="selectedRequest?.name"></span>.
                                    </p>
                                    <div class="mt-4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('leave.Remarks (optional)') }}</label>
                                        <textarea x-model="approveRemarks" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm" placeholder="{{ __('leave.Add any remarks...') }}"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="button" @click="submitApprove()" :disabled="submitting" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:w-auto sm:text-sm disabled:opacity-50">
                            <i class="fas fa-spinner fa-spin mr-2" x-show="submitting"></i>
                            {{ __('leave.Approve') }}
                        </button>
                        <button type="button" @click="showApproveModal = false" :disabled="submitting" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm disabled:opacity-50">
                            {{ __('leave.Cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showRejectModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 transition-opacity" @click="showRejectModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showRejectModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-times text-red-600 dark:text-red-400"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">{{ __('leave.Reject Leave Request') }}</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('leave.You are about to reject the leave request for') }} <span class="font-semibold text-gray-900 dark:text-white" x-text="selectedRequest?.name"></span>.
                                    </p>
                                    <div class="mt-4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('leave.Reason for rejection') }} <span class="text-red-500">*</span></label>
                                        <textarea x-model="rejectRemarks" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm" placeholder="{{ __('leave.Please provide a reason for rejection...') }}" required></textarea>
                                        <p class="mt-1 text-xs text-red-500" x-show="rejectError" x-text="rejectError"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="button" @click="submitReject()" :disabled="submitting" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm disabled:opacity-50">
                            <i class="fas fa-spinner fa-spin mr-2" x-show="submitting"></i>
                            {{ __('leave.Reject') }}
                        </button>
                        <button type="button" @click="showRejectModal = false" :disabled="submitting" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm disabled:opacity-50">
                            {{ __('leave.Cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Details Modal -->
        <div x-show="showViewModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" @click.self="showViewModal = false">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
                <div x-show="showViewModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                
                <div x-show="showViewModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative inline-block align-middle bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all my-8 max-w-3xl w-full" @click.stop>
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/30">
                                    <i class="fas fa-file-alt text-blue-600 dark:text-blue-400"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">{{ __('leave.Leave Request Details') }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400" x-text="selectedRequest?.reference"></p>
                                </div>
                            </div>
                            <button @click="showViewModal = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <!-- Request Information -->
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ __('leave.Request Information') }}</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('leave.Requester') }}</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="selectedRequest?.name"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('leave.Role / Class') }}</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="selectedRequest?.role || selectedRequest?.class"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('leave.Leave Type') }}</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="selectedRequest?.leave_type"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('leave.Status') }}</p>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold" :class="statusClass(selectedRequest?.status)" x-text="titleCase(selectedRequest?.status)"></span>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('leave.From Date') }}</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="formatDate(selectedRequest?.start_date)"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('leave.To Date') }}</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="formatDate(selectedRequest?.end_date)"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('leave.Total Days') }}</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="selectedRequest?.total_days"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('leave.Submitted') }}</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="formatDate(selectedRequest?.submitted_at)"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Reason -->
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">{{ __('leave.Reason') }}</h4>
                                <p class="text-sm text-gray-700 dark:text-gray-300" x-text="selectedRequest?.reason || '—'"></p>
                            </div>

                            <!-- Attachment -->
                            <template x-if="selectedRequest?.attachment_path">
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ __('leave.Attachment') }}</h4>
                                    <div class="text-center">
                                        <img :src="'/storage/' + selectedRequest.attachment_path" alt="Attachment" class="max-w-full max-h-96 mx-auto rounded-lg border border-gray-300 dark:border-gray-600 cursor-pointer" @click="window.open('/storage/' + selectedRequest.attachment_path, '_blank')">
                                        <a :href="'/storage/' + selectedRequest.attachment_path" download class="inline-flex items-center gap-2 mt-3 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                                            <i class="fas fa-download"></i>
                                            {{ __('leave.Download Attachment') }}
                                        </a>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <template x-if="selectedRequest?.status === 'pending'">
                            <button type="button" @click="showViewModal = false; openApproveModal(selectedRequest)" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:w-auto sm:text-sm">
                                <i class="fas fa-check mr-2"></i>
                                {{ __('leave.Approve') }}
                            </button>
                        </template>
                        <template x-if="selectedRequest?.status === 'pending'">
                            <button type="button" @click="showViewModal = false; openRejectModal(selectedRequest)" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm">
                                <i class="fas fa-times mr-2"></i>
                                {{ __('leave.Reject') }}
                            </button>
                        </template>
                        <button type="button" @click="showViewModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                            {{ __('leave.Close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function leaveRequestsPage(config) {
            return {
                routes: config.routes,
                classes: config.classes || [],
                today: config.today,
                csrf: config.csrf,
                tab: config.initialTab || 'staff',
                staffRole: '',
                staffSearch: '',
                staffPending: [],
                staffHistoryRole: '',
                staffHistoryStatus: '',
                staffHistoryDate: '',
                staffHistorySearch: '',
                staffHistory: [],
                studentClass: '',
                studentSearch: '',
                studentPending: [],
                studentHistoryClass: '',
                studentHistoryStatus: '',
                studentHistoryDate: '',
                studentHistorySearch: '',
                studentHistory: [],
                // Modal state
                showApproveModal: false,
                showRejectModal: false,
                showViewModal: false,
                selectedRequest: null,
                approveRemarks: '',
                rejectRemarks: '',
                rejectError: '',
                submitting: false,

                initPage() {
                    // Don't set default date for history - show all history by default
                    this.staffHistoryDate = '';
                    this.studentHistoryDate = '';
                    this.loadStaffPending();
                    this.loadStaffHistory();
                    this.loadStudentPending();
                    this.loadStudentHistory();
                },
                init() {
                    // Watch for tab changes and update URL
                    this.$watch('tab', (value) => {
                        const url = new URL(window.location);
                        url.searchParams.set('tab', value);
                        window.history.pushState({}, '', url);
                    });
                },

                loadStaffPending() {
                    const params = new URLSearchParams({
                        role: this.staffRole || '',
                        search: this.staffSearch || '',
                    });
                    fetch(this.routes.staffPending + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(({ data }) => { this.staffPending = data || []; })
                        .catch(() => { this.staffPending = []; });
                },

                loadStaffHistory() {
                    const params = new URLSearchParams({
                        role: this.staffHistoryRole || '',
                        status: this.staffHistoryStatus || '',
                        date: this.staffHistoryDate || '',
                        search: this.staffHistorySearch || '',
                    });
                    fetch(this.routes.staffHistory + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(({ data }) => { this.staffHistory = data || []; })
                        .catch(() => { this.staffHistory = []; });
                },

                loadStudentPending() {
                    const params = new URLSearchParams({
                        class_id: this.studentClass || '',
                        search: this.studentSearch || '',
                    });
                    fetch(this.routes.studentPending + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(({ data }) => { this.studentPending = data || []; })
                        .catch(() => { this.studentPending = []; });
                },

                loadStudentHistory() {
                    const params = new URLSearchParams({
                        class_id: this.studentHistoryClass || '',
                        status: this.studentHistoryStatus || '',
                        date: this.studentHistoryDate || '',
                        search: this.studentHistorySearch || '',
                    });
                    fetch(this.routes.studentHistory + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(({ data }) => { this.studentHistory = data || []; })
                        .catch(() => { this.studentHistory = []; });
                },

                formatDate(value) {
                    if (!value) return '—';
                    const d = new Date(value);
                    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                },

                statusClass(status) {
                    const map = {
                        'pending': 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-100',
                        'approved': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100',
                        'rejected': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-100',
                    };
                    return map[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                },

                titleCase(value) {
                    if (!value) return '—';
                    return value.charAt(0).toUpperCase() + value.slice(1);
                },

                classLabel(cls) {
                    if (!cls) return '';
                    return cls.grade ? `${cls.grade} • ${cls.name}` : cls.name;
                },

                openApproveModal(request) {
                    this.selectedRequest = request;
                    this.approveRemarks = '';
                    this.showApproveModal = true;
                },

                openRejectModal(request) {
                    this.selectedRequest = request;
                    this.rejectRemarks = '';
                    this.rejectError = '';
                    this.showRejectModal = true;
                },

                openViewModal(request) {
                    this.selectedRequest = request;
                    this.showViewModal = true;
                },

                async submitApprove() {
                    if (!this.selectedRequest) return;
                    this.submitting = true;
                    try {
                        const url = this.routes.approve.replace('{id}', this.selectedRequest.id);
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrf,
                            },
                            body: JSON.stringify({ remarks: this.approveRemarks }),
                        });
                        const result = await response.json();
                        if (response.ok) {
                            this.showApproveModal = false;
                            this.selectedRequest = null;
                            this.loadStaffPending();
                            this.loadStaffHistory();
                            this.loadStudentPending();
                            this.loadStudentHistory();
                        } else {
                            alert(result.message || 'Failed to approve request');
                        }
                    } catch (error) {
                        console.error('Error approving request:', error);
                        alert('An error occurred while approving the request');
                    } finally {
                        this.submitting = false;
                    }
                },

                async submitReject() {
                    if (!this.selectedRequest) return;
                    if (!this.rejectRemarks.trim()) {
                        this.rejectError = '{{ __("leave.Please provide a reason for rejection") }}';
                        return;
                    }
                    this.rejectError = '';
                    this.submitting = true;
                    try {
                        const url = this.routes.reject.replace('{id}', this.selectedRequest.id);
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrf,
                            },
                            body: JSON.stringify({ remarks: this.rejectRemarks }),
                        });
                        const result = await response.json();
                        if (response.ok) {
                            this.showRejectModal = false;
                            this.selectedRequest = null;
                            this.loadStaffPending();
                            this.loadStaffHistory();
                            this.loadStudentPending();
                            this.loadStudentHistory();
                        } else {
                            alert(result.message || 'Failed to reject request');
                        }
                    } catch (error) {
                        console.error('Error rejecting request:', error);
                        alert('An error occurred while rejecting the request');
                    } finally {
                        this.submitting = false;
                    }
                }
            };
        }
    </script>
</x-app-layout>
