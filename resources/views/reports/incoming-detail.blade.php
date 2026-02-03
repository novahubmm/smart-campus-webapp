<x-app-layout>
    <x-slot name="header">
        <x-page-header 
            icon="fas fa-file-alt" 
            iconBg="bg-indigo-50 dark:bg-indigo-900/30" 
            iconColor="text-indigo-700 dark:text-indigo-200" 
            :subtitle="$report->created_at->format('M d, Y')" 
            :title="__('report.Daily Reports')" 
        />
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-back-link 
                :href="route('reports.index', ['tab' => $report->direction === 'outgoing' ? 'outgoing' : 'incoming'])"
                :text="__('report.Back to Reports')"
            />

            <!-- Report Header -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                                {{ $report->subject }}
                            </h2>
                            @php
                                $categoryColors = [
                                    'suggestion' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                                    'complaint' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                                    'feedback' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                                    'report' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
                                    'request' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
                                    'notice' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                                    'reminder' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300',
                                ];
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
                                    'reviewed' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                                    'resolved' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                                    'approved' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                                    'acknowledged' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                                    'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $categoryColors[$report->category] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ __('report.' . ucfirst($report->category)) }}
                            </span>
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$report->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ __('report.' . ucfirst($report->status)) }}
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                            @if($report->direction === 'outgoing')
                                <span><i class="fas fa-user mr-1"></i>{{ __('report.From:') }} {{ $report->user?->name ?? __('Admin') }}</span>
                                <span><i class="fas fa-paper-plane mr-1"></i>{{ __('report.To:') }} {{ $report->recipientUser?->name ?? __('report.Unknown') }}</span>
                            @else
                                <span><i class="fas fa-user mr-1"></i>{{ __('report.From:') }} {{ $report->user?->name ?? __('report.Unknown') }}</span>
                                <span><i class="fas fa-paper-plane mr-1"></i>{{ __('report.To:') }} {{ ucfirst($report->recipient) }}</span>
                            @endif
                            <span><i class="fas fa-calendar mr-1"></i>{{ $report->created_at->format('l, F d, Y') }}</span>
                            <span><i class="fas fa-clock mr-1"></i>{{ $report->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Content -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ __('report.Message') }}</h3>
                    <div class="prose prose-sm dark:prose-invert max-w-none text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ $report->message }}</div>
                </div>
            </div>

            <!-- Admin Remarks (if any) -->
            @if($report->admin_remarks)
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
                    <div class="p-6">
                        <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-3">
                            <i class="fas fa-comment-dots mr-2"></i>{{ __('report.Admin Remarks') }}
                        </h3>
                        <div class="prose prose-sm max-w-none text-blue-800 dark:text-blue-200 whitespace-pre-line">{{ $report->admin_remarks }}</div>
                        @if($report->reviewedBy)
                            <p class="mt-3 text-xs text-blue-600 dark:text-blue-400">
                                {{ __('report.Reviewed by') }} {{ $report->reviewedBy->name }} 
                                @if($report->reviewed_at)
                                    {{ __('report.on') }} {{ $report->reviewed_at->format('M d, Y h:i A') }}
                                @endif
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Actions for incoming reports -->
            @if($report->direction === 'incoming' && $report->status === 'pending')
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">{{ __('report.Review Report') }}</h3>
                    <form action="{{ route('reports.incoming.review', $report->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('report.Admin Remarks') }}</label>
                            <textarea name="admin_remarks" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" placeholder="{{ __('report.Add your remarks here...') }}"></textarea>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="submit" name="status" value="reviewed" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <i class="fas fa-check mr-1"></i>{{ __('report.Mark as Reviewed') }}
                            </button>
                            <button type="submit" name="status" value="resolved" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <i class="fas fa-check-double mr-1"></i>{{ __('report.Mark as Resolved') }}
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
