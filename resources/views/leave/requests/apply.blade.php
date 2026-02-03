<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-violet-600 text-white shadow-lg">
                <i class="fas fa-calendar-times"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('leave.Leave Requests') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('leave.Leave Request') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden" x-data="leaveApplyPage(@js([
        'routes' => [
            'store' => route('leave-requests.store'),
            'history' => route('leave-requests.my'),
        ],
        'today' => $today,
    ]))" x-init="initPage()">
        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('leave.Submit Leave Request') }}</h3>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('leave-requests.index') }}" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <i class="fas fa-arrow-left"></i>{{ __('leave.Back') }}
                        </a>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('leave.Leave Type') }}</label>
                            <select x-model="form.leave_type" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-purple-500 focus:ring-purple-500" required>
                                <option value="">{{ __('leave.Select Leave Type') }}</option>
                                <option value="sick">{{ __('leave.Sick Leave') }}</option>
                                <option value="casual">{{ __('leave.Casual Leave') }}</option>
                                <option value="emergency">{{ __('leave.Emergency Leave') }}</option>
                                <option value="other">{{ __('leave.Other') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('leave.From Date') }}</label>
                            <input type="date" x-model="form.start_date" :min="today" @change="syncDates" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-purple-500 focus:ring-purple-500" required />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('leave.To Date') }}</label>
                            <input type="date" x-model="form.end_date" :min="form.start_date || today" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-purple-500 focus:ring-purple-500" required />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('leave.Total Days') }}</label>
                            <input type="text" :value="totalDays" readonly class="w-full rounded-lg border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-200" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('leave.Reason') }}</label>
                        <textarea x-model="form.reason" rows="4" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-purple-500 focus:ring-purple-500" placeholder="{{ __('leave.Provide details for your leave request') }}" required></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button type="button" @click="resetForm" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <i class="fas fa-redo"></i>{{ __('leave.Reset') }}
                        </button>
                        <button type="submit" :disabled="submitting" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 disabled:opacity-60">
                            <i class="fas" :class="submitting ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                            <span>{{ __('leave.Submit Request') }}</span>
                        </button>
                    </div>
                    <p x-show="error" class="text-sm text-red-600" x-text="error"></p>
                    <p x-show="success" class="text-sm text-green-600" x-text="success"></p>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('leave.My Leave Request History') }}</h3>
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ __('leave.Select Date:') }}</label>
                            <input type="date" x-model="historyDate" @change="loadHistory" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm focus:border-purple-500 focus:ring-purple-500" />
                        </div>
                        <button type="button" @click="resetDate" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <i class="fas fa-calendar-day"></i>{{ __('leave.Today') }}
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto p-4">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Request ID') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Leave Type') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.From Date') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.To Date') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Days') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Status') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Submitted') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('leave.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700" x-show="paginatedHistory.length">
                            <template x-for="row in paginatedHistory" :key="row.id">
                                <tr>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white" x-text="row.reference"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.leave_type"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatDate(row.start_date)"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatDate(row.end_date)"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.total_days"></td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold" :class="statusClass(row.status)" x-text="titleCase(row.status)"></span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatDate(row.submitted_at)"></td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end">
                                            <button type="button" @click="viewRequest(row)" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 flex items-center justify-center hover:border-purple-400 hover:text-purple-500" title="{{ __('leave.View Details') }}">
                                                <i class="fas fa-eye text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tbody x-show="!history.length">
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('leave.No leave requests yet.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- History Pagination -->
                    <div x-show="history.length > perPage" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('pagination.Showing') }} <span x-text="Math.min((historyCurrentPage - 1) * perPage + 1, history.length)"></span> {{ __('pagination.to') }} <span x-text="Math.min(historyCurrentPage * perPage, history.length)"></span> {{ __('pagination.of') }} <span x-text="history.length"></span> {{ __('pagination.results') }}
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" @click="historyCurrentPage = 1" :disabled="historyCurrentPage === 1" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-angle-double-left"></i>
                            </button>
                            <button type="button" @click="historyCurrentPage--" :disabled="historyCurrentPage === 1" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-angle-left"></i>
                            </button>
                            <template x-for="page in historyVisiblePages" :key="page">
                                <button type="button" @click="historyCurrentPage = page" :class="page === historyCurrentPage ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-3 py-1.5 text-sm font-medium rounded-lg border" x-text="page"></button>
                            </template>
                            <button type="button" @click="historyCurrentPage++" :disabled="historyCurrentPage === historyTotalPages" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-angle-right"></i>
                            </button>
                            <button type="button" @click="historyCurrentPage = historyTotalPages" :disabled="historyCurrentPage === historyTotalPages" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-angle-double-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function leaveApplyPage(config) {
            return {
                routes: config.routes,
                today: config.today,
                form: {
                    leave_type: '',
                    start_date: config.today,
                    end_date: config.today,
                    reason: '',
                },
                submitting: false,
                success: '',
                error: '',
                historyDate: config.today,
                history: [],
                
                // Pagination
                perPage: 10,
                historyCurrentPage: 1,

                initPage() {
                    this.loadHistory();
                },

                syncDates() {
                    if (this.form.end_date < this.form.start_date) {
                        this.form.end_date = this.form.start_date;
                    }
                },

                get totalDays() {
                    if (!this.form.start_date || !this.form.end_date) return 0;
                    const start = new Date(this.form.start_date);
                    const end = new Date(this.form.end_date);
                    return Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
                },

                submit() {
                    this.error = '';
                    this.success = '';
                    this.submitting = true;
                    fetch(this.routes.store, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify(this.form),
                    })
                        .then(async (r) => {
                            const data = await r.json();
                            if (!r.ok) {
                                throw new Error(data.message || 'Failed to submit');
                            }
                            this.success = '{{ __('leave.Leave request submitted successfully.') }}';
                            this.loadHistory();
                            this.resetForm();
                        })
                        .catch((e) => {
                            this.error = e.message || 'Error submitting request';
                        })
                        .finally(() => {
                            this.submitting = false;
                        });
                },

                resetForm() {
                    this.form.leave_type = '';
                    this.form.start_date = this.today;
                    this.form.end_date = this.today;
                    this.form.reason = '';
                },

                loadHistory() {
                    const params = new URLSearchParams({ date: this.historyDate || '' });
                    fetch(this.routes.history + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(({ data }) => { 
                            this.history = data || []; 
                            this.historyCurrentPage = 1;
                        })
                        .catch(() => { this.history = []; });
                },

                // Pagination computed properties
                get historyTotalPages() { return Math.ceil(this.history.length / this.perPage) || 1; },
                get paginatedHistory() {
                    const start = (this.historyCurrentPage - 1) * this.perPage;
                    return this.history.slice(start, start + this.perPage);
                },
                get historyVisiblePages() {
                    const pages = [];
                    let start = Math.max(1, this.historyCurrentPage - 2);
                    let end = Math.min(this.historyTotalPages, start + 4);
                    if (end - start < 4) start = Math.max(1, end - 4);
                    for (let i = start; i <= end; i++) pages.push(i);
                    return pages;
                },

                resetDate() {
                    this.historyDate = this.today;
                    this.loadHistory();
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

                viewRequest(row) {
                    // Show request details in an alert (can be replaced with modal later)
                    const details = `Request ID: ${row.reference}\nLeave Type: ${row.leave_type}\nFrom: ${this.formatDate(row.start_date)}\nTo: ${this.formatDate(row.end_date)}\nDays: ${row.total_days}\nStatus: ${this.titleCase(row.status)}\nReason: ${row.reason || 'N/A'}`;
                    alert(details);
                },
            };
        }
    </script>
</x-app-layout>
