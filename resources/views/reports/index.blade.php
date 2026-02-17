<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white shadow-lg">
                <i class="fas fa-file-alt"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('report.Communication') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('report.Daily Reports') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10" x-data="reportManager()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Tab Navigation -->
            <div class="academic-structure-section">
                <div class="academic-tabs">
                    <a href="{{ route('reports.index', ['tab' => 'incoming']) }}" 
                       class="academic-tab {{ $tab === 'incoming' ? 'active' : '' }}">
                        {{ __('report.Received from Teachers') }}
                        @if($incomingStats['pending'] > 0)
                            <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">{{ $incomingStats['pending'] }}</span>
                        @endif
                    </a>
                    <a href="{{ route('reports.index', ['tab' => 'outgoing']) }}" 
                       class="academic-tab {{ $tab === 'outgoing' ? 'active' : '' }}">
                        {{ __('report.Sent to Teachers') }}
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @if($tab === 'incoming')
                    <x-stat-card icon="fas fa-inbox" :title="__('report.Total Received')" :number="$incomingStats['total']" :subtitle="__('report.From teachers')" />
                    <x-stat-card icon="fas fa-clock" :title="__('report.Pending Review')" :number="$incomingStats['pending']" :subtitle="__('report.Awaiting action')" />
                    <x-stat-card icon="fas fa-check-circle" :title="__('report.Reviewed')" :number="$incomingStats['reviewed']" :subtitle="__('report.Completed')" />
                @else
                    <x-stat-card icon="fas fa-paper-plane" :title="__('report.Total Sent')" :number="$outgoingStats['total']" :subtitle="__('report.To teachers')" />
                    <x-stat-card icon="fas fa-clock" :title="__('report.Pending')" :number="$outgoingStats['pending']" :subtitle="__('report.Not yet read')" />
                    <x-stat-card icon="fas fa-check-double" :title="__('report.Acknowledged')" :number="$outgoingStats['acknowledged']" :subtitle="__('report.Read by teacher')" />
                @endif
            </div>

            <!-- Main Card -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $tab === 'incoming' ? __('report.Reports from Teachers') : __('report.Reports Sent to Teachers') }}
                    </h3>
                    @if($tab === 'outgoing')
                        <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700" @click="openModal()">
                            <i class="fas fa-plus"></i>{{ __('report.Send Report') }}
                        </button>
                    @endif
                </div>

                <!-- Filters -->
                <form method="GET" action="{{ route('reports.index') }}" class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ __('report.Search') }}</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('report.Subject, message...') }}" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ __('report.Status') }}</label>
                            <select name="status" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('report.All Status') }}</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('report.Pending') }}</option>
                                <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>{{ __('report.Reviewed') }}</option>
                                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>{{ __('report.Resolved') }}</option>
                                <option value="acknowledged" {{ request('status') === 'acknowledged' ? 'selected' : '' }}>{{ __('report.Acknowledged') }}</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ __('report.Category') }}</label>
                            <select name="category" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('report.All Categories') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>{{ __('report.' . ucfirst($category)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ __('report.From Date') }}</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ __('report.To Date') }}</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 px-3 py-2 text-sm font-semibold rounded-lg text-white bg-gray-800 dark:bg-gray-700 hover:bg-gray-900 dark:hover:bg-gray-600">{{ __('report.Apply') }}</button>
                            <a href="{{ route('reports.index', ['tab' => $tab]) }}" class="px-3 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">{{ __('report.Reset') }}</a>
                        </div>
                    </div>
                </form>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                    {{ $tab === 'incoming' ? __('report.From') : __('report.To') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('report.Subject') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('report.Category') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('report.Status') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('report.Date') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('report.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($reports as $report)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                                                <i class="fas fa-user text-indigo-600 dark:text-indigo-400 text-xs"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                    {{ $tab === 'incoming' ? ($report->user?->name ?? __('report.Unknown')) : ($report->recipientUser?->name ?? __('report.Unknown')) }}
                                                </p>
                                                @if($tab === 'incoming' && $report->recipient)
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('report.To:') }} {{ ucfirst($report->recipient) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $report->subject }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ Str::limit($report->message, 50) }}</p>
                                    </td>
                                    <td class="px-4 py-3">
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
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $categoryColors[$report->category] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                            {{ __('report.' . ucfirst($report->category)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $statusColors = [
                                                'pending' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
                                                'reviewed' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                                                'resolved' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                                                'approved' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                                                'acknowledged' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                                                'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $statusColors[$report->status] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ __('report.' . ucfirst($report->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                        {{ $report->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('reports.incoming.show', $report->id) }}" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-blue-500 flex items-center justify-center hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30" title="{{ __('report.View') }}">
                                                <i class="fas fa-eye text-xs"></i>
                                            </a>
                                            <button type="button" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-red-500 flex items-center justify-center hover:border-red-400 hover:bg-red-50 dark:hover:bg-red-900/30" title="{{ __('report.Delete') }}" @click="confirmDelete('{{ $report->id }}')">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                            <i class="fas fa-inbox text-4xl mb-3 opacity-50"></i>
                                            <p class="text-sm">{{ __('report.No reports found') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($reports->hasPages())
                    {{ $reports->withQueryString()->links() }}
                @endif
            </div>
        </div>

        <!-- Create Report Modal -->
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal()">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-2xl shadow-2xl" @click.stop>
                    <form action="{{ route('reports.store') }}" method="POST">
                        @csrf
                        
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-t-xl">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-lg">
                                    <i class="fas fa-paper-plane"></i>
                                </span>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('report.Send Report to Teacher') }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('report.Teacher will see this in their app') }}</p>
                                </div>
                            </div>
                            <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" @click="closeModal()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <!-- Modal Body -->
                        <div class="p-5 space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('report.Select Teacher') }} <span class="text-red-500">*</span></label>
                                <select name="recipient_user_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">{{ __('report.Choose a teacher...') }}</option>
                                    @foreach($teachers as $teacher)
                                        @if($teacher->user)
                                            <option value="{{ $teacher->user->id }}">{{ $teacher->user->name }} [{{ $teacher->employee_id }}]</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('report.Category') }} <span class="text-red-500">*</span></label>
                                <select name="category" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">{{ __('report.Select category...') }}</option>
                                    <option value="notice">{{ __('report.Notice') }}</option>
                                    <option value="reminder">{{ __('report.Reminder') }}</option>
                                    <option value="request">{{ __('report.Request') }}</option>
                                    <option value="feedback">{{ __('report.Feedback') }}</option>
                                    <option value="report">{{ __('report.Report') }}</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('report.Subject') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="subject" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('report.Enter subject...') }}" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('report.Message') }} <span class="text-red-500">*</span></label>
                                <textarea name="message" rows="5" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('report.Enter your message...') }}" required></textarea>
                            </div>
                        </div>
                        
                        <!-- Modal Footer -->
                        <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                            <button type="button" class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="closeModal()">
                                {{ __('report.Cancel') }}
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700">
                                <i class="fas fa-paper-plane mr-2"></i>{{ __('report.Send Report') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation -->
        <form x-ref="deleteForm" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>

    @push('scripts')
    <script>
        function reportManager() {
            return {
                showModal: false,
                
                openModal() {
                    this.showModal = true;
                },
                
                closeModal() {
                    this.showModal = false;
                },
                
                confirmDelete(id) {
                    if (confirm('{{ __("report.Are you sure you want to delete this report?") }}')) {
                        this.$refs.deleteForm.action = `{{ url('reports') }}/${id}`;
                        this.$refs.deleteForm.submit();
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
