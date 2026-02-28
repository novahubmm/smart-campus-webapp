<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                <i class="fas fa-inbox"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Communications</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Guardian Inbox</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800 dark:border-green-900/50 dark:bg-green-900/30 dark:text-green-100">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-stat-card 
                    icon="fas fa-inbox"
                    title="Total Messages"
                    :number="$stats['total'] ?? 0"
                    subtitle="All time"
                />
                <x-stat-card 
                    icon="fas fa-envelope"
                    title="Unread"
                    :number="$stats['unread'] ?? 0"
                    subtitle="Requires attention"
                />
                <x-stat-card 
                    icon="fas fa-check-circle"
                    title="Resolved"
                    :number="$stats['resolved'] ?? 0"
                    subtitle="Handled queries"
                />
            </div>

            <!-- Main Content -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">All Messages</h3>
                </div>

                <!-- Filter Bar -->
                <form method="GET" action="{{ route('inbox.index') }}" class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">Status</label>
                            <select name="status" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">All Statuses</option>
                                <option value="unread" @selected(request('status') === 'unread')>Unread</option>
                                <option value="read" @selected(request('status') === 'read')>Read</option>
                                <option value="assigned" @selected(request('status') === 'assigned')>Assigned</option>
                                <option value="resolved" @selected(request('status') === 'resolved')>Resolved</option>
                                <option value="closed" @selected(request('status') === 'closed')>Closed</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">Grade</label>
                            <select name="grade_id" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">All Grades</option>
                                @foreach($grades as $grade)
                                    <option value="{{ $grade->id }}" @selected(request('grade_id') === $grade->id)>{{ $grade->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">Class</label>
                            <select name="class_id" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">All Classes</option>
                                @foreach($classes as $schoolClass)
                                    <option value="{{ $schoolClass->id }}" @selected(request('class_id') === $schoolClass->id)>{{ $schoolClass->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 px-3 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700">Filter</button>
                            <a href="{{ route('inbox.index') }}" class="px-3 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Reset</a>
                        </div>
                    </div>
                </form>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">Sender</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">Subject & Category</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">Student Details</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">Status & Priority</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">Date</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($messages as $message)
                                @php
                                    $statusStyles = [
                                        'unread' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                        'read' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                        'assigned' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                                        'resolved' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                        'closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                    ];
                                    $priorityStyles = [
                                        'high' => 'text-red-500',
                                        'medium' => 'text-amber-500',
                                        'low' => 'text-green-500',
                                    ];
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ $message->status === 'unread' ? 'bg-blue-50/30 dark:bg-blue-900/10' : '' }}">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 shrink-0">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                    {{ $message->guardianProfile->user->first_name ?? 'Guardian' }} {{ $message->guardianProfile->user->last_name ?? '' }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Guardian</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white {{ $message->status === 'unread' ? 'font-bold' : '' }}">
                                            {{ $message->subject }}
                                        </p>
                                        <span class="inline-flex text-xs text-gray-500 dark:text-gray-400">
                                            Category: {{ ucfirst($message->category) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($message->studentProfile)
                                            <p class="text-sm text-gray-900 dark:text-white">{{ $message->studentProfile->user->first_name ?? 'Student' }} {{ $message->studentProfile->user->last_name ?? '' }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $message->studentProfile->grade->name ?? 'N/A' }} - {{ $message->studentProfile->classModel->name ?? 'N/A' }}
                                            </p>
                                        @else
                                            <span class="text-gray-400 text-sm">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 space-y-1">
                                        <div>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusStyles[$message->status] ?? $statusStyles['read'] }}">
                                                {{ ucfirst($message->status) }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium {{ $priorityStyles[$message->priority] ?? 'text-gray-500' }}">
                                                <i class="fas fa-flag align-middle mr-1"></i>{{ ucfirst($message->priority) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white leading-tight">
                                        <span class="font-medium">{{ $message->created_at->format('M j, Y') }}</span>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $message->created_at->format('g:i A') }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('inbox.show', $message) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg text-blue-600 bg-blue-50 hover:bg-blue-100 dark:text-blue-400 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 transition-colors">
                                                View <i class="fas fa-arrow-right text-xs"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                            <i class="fas fa-inbox text-4xl mb-3 opacity-50"></i>
                                            <p class="text-sm">No inbox messages found</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($messages->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                        {{ $messages->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
