<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('system-admin.feedback.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-red-600 text-white shadow-lg">
                    <i class="fas fa-comment-alt"></i>
                </span>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Feedback Details</p>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $feedback->subject }}</h2>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <x-alert-success>{{ session('success') }}</x-alert-success>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Feedback Details -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Feedback Message</h3>
                            <div class="prose dark:prose-invert max-w-none">
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $feedback->message }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Notes -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Admin Notes</h3>
                            <form method="POST" action="{{ route('system-admin.feedback.update', $feedback) }}">
                                @csrf
                                @method('PUT')
                                
                                <div class="space-y-4">
                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Status
                                        </label>
                                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            <option value="pending" {{ $feedback->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="reviewed" {{ $feedback->status === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                                            <option value="resolved" {{ $feedback->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                            <option value="closed" {{ $feedback->status === 'closed' ? 'selected' : '' }}>Closed</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="admin_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Notes
                                        </label>
                                        <textarea 
                                            id="admin_notes" 
                                            name="admin_notes" 
                                            rows="4" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            placeholder="Add internal notes about this feedback..."
                                        >{{ old('admin_notes', $feedback->admin_notes) }}</textarea>
                                    </div>

                                    <div class="flex justify-end">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                            <i class="fas fa-save mr-2"></i>
                                            Update
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Submitter Info -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Submitter Information</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Name</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $feedback->name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Email</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $feedback->email }}</p>
                                </div>
                                @if($feedback->phone)
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Phone</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $feedback->phone }}</p>
                                </div>
                                @endif
                                @if($feedback->user)
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">User Account</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $feedback->user->name }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Feedback Meta -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Details</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Type</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($feedback->type === 'bug') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-400
                                        @elseif($feedback->type === 'feature_request') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-400
                                        @elseif($feedback->type === 'complaint') bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-400
                                        @elseif($feedback->type === 'suggestion') bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-400
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $feedback->type)) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Status</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($feedback->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-400
                                        @elseif($feedback->status === 'reviewed') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-400
                                        @elseif($feedback->status === 'resolved') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                        @endif">
                                        {{ ucfirst($feedback->status) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Submitted</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $feedback->created_at->format('M d, Y h:i A') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Last Updated</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $feedback->updated_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
