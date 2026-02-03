<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-lg">
                <i class="fas fa-chart-line"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('activity_logs.System') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('activity_logs.User Activity Logs') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center text-xl shadow-lg">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('activity_logs.Active Users') }}</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['active_users']) }}</p>
                        <p class="text-xs text-blue-600 dark:text-blue-400">{{ $filter->dateRangeLabel() }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 text-white flex items-center justify-center text-xl shadow-lg">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('activity_logs.Logins') }}</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['logins']) }}</p>
                        <p class="text-xs text-green-600 dark:text-green-400">{{ $filter->dateRangeLabel() }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 text-white flex items-center justify-center text-xl shadow-lg">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('activity_logs.Alerts') }}</p>
                        <p class="text-2xl font-bold {{ $stats['alerts'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">{{ number_format($stats['alerts']) }}</p>
                        <p class="text-xs {{ $stats['alerts'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }}">{{ $stats['alerts'] > 0 ? __('Needs review') : __('All clear') }}</p>
                    </div>
                </div>
            </div>

            <!-- Activity Log Table -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('activity_logs.Recent Activity') }}</h3>
                </div>

                <!-- Filters -->
                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                    <form method="GET" action="{{ route('user-activity-logs.index') }}" class="flex flex-wrap items-center gap-3">
                        <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ __('activity_logs.Filters:') }}</span>
                        <select name="date_range" class="form-select-sm">
                            <option value="today" {{ ($filter->dateRange ?? 'today') === 'today' ? 'selected' : '' }}>{{ __('activity_logs.Today') }}</option>
                            <option value="yesterday" {{ $filter->dateRange === 'yesterday' ? 'selected' : '' }}>{{ __('activity_logs.Yesterday') }}</option>
                            <option value="last_7_days" {{ $filter->dateRange === 'last_7_days' ? 'selected' : '' }}>{{ __('activity_logs.Last 7 Days') }}</option>
                            <option value="last_30_days" {{ $filter->dateRange === 'last_30_days' ? 'selected' : '' }}>{{ __('activity_logs.Last 30 Days') }}</option>
                            <option value="this_month" {{ $filter->dateRange === 'this_month' ? 'selected' : '' }}>{{ __('activity_logs.This Month') }}</option>
                            <option value="all" {{ $filter->dateRange === 'all' ? 'selected' : '' }}>{{ __('activity_logs.All Time') }}</option>
                        </select>
                        <select name="action" class="form-select-sm">
                            <option value="">{{ __('activity_logs.All Actions') }}</option>
                            @foreach($actionTypes as $key => $label)
                            <option value="{{ $key }}" {{ $filter->action === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="form-select-sm">
                            <option value="">{{ __('activity_logs.All Status') }}</option>
                            <option value="ok" {{ $filter->status === 'ok' ? 'selected' : '' }}>{{ __('activity_logs.OK') }}</option>
                            <option value="alert" {{ $filter->status === 'alert' ? 'selected' : '' }}>{{ __('activity_logs.Alert') }}</option>
                        </select>
                        <input type="text" name="search" value="{{ $filter->search }}" placeholder="{{ __('activity_logs.Search user or IP...') }}" class="form-input-sm">
                        <button type="submit" class="btn-filter">{{ __('activity_logs.Apply') }}</button>
                        <a href="{{ route('user-activity-logs.index') }}" class="btn-filter-reset">{{ __('activity_logs.Reset') }}</a>
                    </form>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full activity-table">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="th-cell">{{ __('activity_logs.Time') }}</th>
                                <th class="th-cell">{{ __('activity_logs.User') }}</th>
                                <th class="th-cell">{{ __('activity_logs.Role') }}</th>
                                <th class="th-cell">{{ __('activity_logs.Action') }}</th>
                                <th class="th-cell">{{ __('activity_logs.IP Address') }}</th>
                                <th class="th-cell">{{ __('activity_logs.Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($logs as $log)
                            <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="td-cell">
                                    <div class="text-gray-900 dark:text-white">{{ $log->created_at->format('Y-m-d') }}</div>
                                    <div class="text-xs text-gray-500">{{ $log->created_at->format('H:i:s') }}</div>
                                </td>
                                <td class="td-cell">
                                    @if($log->user)
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $log->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $log->user->email }}</div>
                                    @else
                                    <span class="text-gray-500 dark:text-gray-400">{{ __('activity_logs.Unknown') }}</span>
                                    @endif
                                </td>
                                <td class="td-cell">
                                    @if($log->user)
                                    <span class="role-badge">{{ ucfirst($log->user->roles->first()?->name ?? 'User') }}</span>
                                    @else
                                    <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="td-cell">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $log->action_label }}</div>
                                    @if($log->description)
                                    <div class="text-xs text-gray-500">{{ Str::limit($log->description, 40) }}</div>
                                    @endif
                                </td>
                                <td class="td-cell">
                                    <code class="ip-badge">{{ $log->ip_address ?? '-' }}</code>
                                </td>
                                <td class="td-cell">
                                    @if($log->status === 'alert')
                                    <span class="status-badge alert"><i class="fas fa-exclamation-circle mr-1"></i>{{ __('activity_logs.Alert') }}</span>
                                    @else
                                    <span class="status-badge ok"><i class="fas fa-check-circle mr-1"></i>{{ __('activity_logs.OK') }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="td-empty">
                                    <div class="flex flex-col items-center py-8">
                                        <i class="fas fa-inbox text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                        <p class="text-gray-500 dark:text-gray-400">{{ __('activity_logs.No activity logs found for the selected filters.') }}</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $logs->withQueryString()->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .activity-table { width: 100%; border-collapse: collapse; }
        .th-cell { padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; white-space: nowrap; }
        .dark .th-cell { color: #9ca3af; }
        .td-cell { padding: 12px 16px; font-size: 14px; color: #374151; white-space: nowrap; }
        .dark .td-cell { color: #e5e7eb; }
        .td-empty { padding: 40px 16px; text-align: center; color: #9ca3af; }

        .form-select-sm { padding: 8px 12px; font-size: 13px; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; color: #374151; }
        .dark .form-select-sm { background: #374151; border-color: #4b5563; color: #e5e7eb; }
        .form-input-sm { padding: 8px 12px; font-size: 13px; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; color: #374151; min-width: 180px; }
        .dark .form-input-sm { background: #374151; border-color: #4b5563; color: #e5e7eb; }

        .btn-filter { padding: 8px 16px; font-size: 13px; font-weight: 500; color: #fff; background: linear-gradient(135deg, #3b82f6, #6366f1); border-radius: 8px; transition: all 0.2s; }
        .btn-filter:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-filter-reset { padding: 8px 16px; font-size: 13px; font-weight: 500; color: #6b7280; background: #f3f4f6; border-radius: 8px; transition: all 0.2s; }
        .btn-filter-reset:hover { background: #e5e7eb; }
        .dark .btn-filter-reset { background: #374151; color: #d1d5db; }
        .dark .btn-filter-reset:hover { background: #4b5563; }

        .role-badge { display: inline-flex; padding: 4px 10px; font-size: 12px; font-weight: 500; border-radius: 6px; background: #eff6ff; color: #1d4ed8; }
        .dark .role-badge { background: #1e3a5f; color: #93c5fd; }

        .ip-badge { display: inline-flex; padding: 4px 8px; font-size: 11px; font-family: monospace; border-radius: 4px; background: #f3f4f6; color: #6b7280; }
        .dark .ip-badge { background: #374151; color: #9ca3af; }

        .status-badge { display: inline-flex; align-items: center; padding: 4px 10px; font-size: 12px; font-weight: 500; border-radius: 6px; }
        .status-badge.ok { background: #ecfdf5; color: #059669; }
        .dark .status-badge.ok { background: #064e3b; color: #6ee7b7; }
        .status-badge.alert { background: #fef2f2; color: #dc2626; }
        .dark .status-badge.alert { background: #450a0a; color: #fca5a5; }
    </style>
</x-app-layout>
