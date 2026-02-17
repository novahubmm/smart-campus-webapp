<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-red-600 text-white shadow-lg">
                    <i class="fas fa-comments"></i>
                </span>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">System Administration</p>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Feedback Management</h2>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <x-alert-success>{{ session('success') }}</x-alert-success>
            @endif

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <x-stat-card 
                    icon="fas fa-inbox"
                    title="Total Feedback"
                    :number="$feedbacks->total()"
                    subtitle="All submissions"
                />
                <x-stat-card 
                    icon="fas fa-clock"
                    title="Pending"
                    :number="$feedbacks->where('status', 'pending')->count()"
                    subtitle="Awaiting review"
                />
                <x-stat-card 
                    icon="fas fa-eye"
                    title="Reviewed"
                    :number="$feedbacks->where('status', 'reviewed')->count()"
                    subtitle="Under review"
                />
                <x-stat-card 
                    icon="fas fa-check-circle"
                    title="Resolved"
                    :number="$feedbacks->where('status', 'resolved')->count()"
                    subtitle="Completed"
                />
            </div>

            <!-- Feedback List -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Submitter
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Subject
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($feedbacks as $feedback)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                        <i class="fas fa-user text-gray-500 dark:text-gray-400"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ $feedback->name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $feedback->email }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900 dark:text-white">{{ Str::limit($feedback->subject, 50) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($feedback->type === 'bug') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-400
                                                @elseif($feedback->type === 'feature_request') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-400
                                                @elseif($feedback->type === 'complaint') bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-400
                                                @elseif($feedback->type === 'suggestion') bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-400
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                                @endif">
                                                {{ ucfirst(str_replace('_', ' ', $feedback->type)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($feedback->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-400
                                                @elseif($feedback->status === 'reviewed') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-400
                                                @elseif($feedback->status === 'resolved') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                                @endif">
                                                {{ ucfirst($feedback->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $feedback->created_at->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('system-admin.feedback.show', $feedback) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                <i class="fas fa-eye mr-1"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                            No feedback submissions yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $feedbacks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
