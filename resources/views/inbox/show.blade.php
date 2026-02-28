<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('inbox.index') }}"
                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <span
                class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                <i class="fas fa-inbox"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Thread Details</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $inbox->subject }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div
                    class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800 dark:border-green-900/50 dark:bg-green-900/30 dark:text-green-100">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div
                    class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800 dark:border-red-900/50 dark:bg-red-900/30 dark:text-red-100">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex flex-col gap-6">
                <!-- Top Details & Actions -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 w-full">
                    <!-- Message Details -->
                    <div
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-5 h-full">
                        <h4
                            class="text-base font-semibold text-gray-900 dark:text-white mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
                            Information</h4>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span
                                        class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Category</span>
                                    <span
                                        class="inline-flex px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        {{ ucfirst($inbox->category) }}
                                    </span>
                                </div>
                                <div>
                                    <span
                                        class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Priority</span>
                                    <span
                                        class="inline-flex px-2 py-1 rounded text-xs font-medium 
                                        @if($inbox->priority === 'high') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 
                                        @elseif($inbox->priority === 'medium') bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300
                                        @else bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 @endif">
                                        <i class="fas fa-flag mr-1.5 mt-0.5"></i> {{ ucfirst($inbox->priority) }}
                                    </span>
                                </div>
                            </div>
                            @if($inbox->studentProfile)
                                <div>
                                    <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Related
                                        Student</span>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                        {{ $inbox->studentProfile->user->first_name ?? 'Student' }}
                                        {{ $inbox->studentProfile->user->last_name ?? '' }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Grade: {{ $inbox->studentProfile->grade->name ?? 'N/A' }} | Class:
                                        {{ $inbox->studentProfile->classModel->name ?? 'N/A' }}
                                    </p>
                                </div>
                            @endif
                            <div>
                                <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Assigned
                                    To</span>
                                @if($inbox->assignedTo)
                                    <div class="flex items-center gap-2 mt-1">
                                        <div
                                            class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xs shrink-0">
                                            <i class="fas fa-chalkboard-teacher"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                            {{ $inbox->assignedTo->user->first_name ?? 'Teacher' }}
                                            {{ $inbox->assignedTo->user->last_name ?? '' }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-sm italic text-gray-500 dark:text-gray-400">Unassigned</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Update Status -->
                    <div
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-5 h-full">
                        <h4
                            class="text-base font-semibold text-gray-900 dark:text-white mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
                            Actions</h4>

                        <form action="{{ route('inbox.update-status', $inbox) }}" method="POST"
                            class="mb-5 border-b border-gray-100 dark:border-gray-700 pb-5">
                            @csrf
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Change
                                Status</label>
                            <div class="flex gap-2">
                                <select name="status"
                                    class="flex-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="unread" @selected($inbox->status === 'unread')>Unread</option>
                                    <option value="read" @selected($inbox->status === 'read')>Read</option>
                                    <option value="assigned" @selected($inbox->status === 'assigned')>Assigned</option>
                                    <option value="resolved" @selected($inbox->status === 'resolved')>Resolved</option>
                                    <option value="closed" @selected($inbox->status === 'closed')>Closed</option>
                                </select>
                                <button type="submit"
                                    class="px-3 py-2 text-sm font-semibold rounded-lg text-white bg-gray-800 hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 transition-colors">
                                    Update
                                </button>
                            </div>
                        </form>

                        <form action="{{ route('inbox.assign', $inbox) }}" method="POST">
                            @csrf
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Assign to
                                Teacher</label>
                            <div class="space-y-3">
                                <select name="teacher_profile_id" required
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select a Teacher</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}"
                                            @selected($inbox->assigned_to_type === \App\Models\TeacherProfile::class && $inbox->assigned_to_id == $teacher->id)>
                                            {{ $teacher->user->first_name ?? 'Teacher' }}
                                            {{ $teacher->user->last_name ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit"
                                    class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 hover:border-indigo-300 dark:bg-indigo-900/30 dark:text-indigo-300 dark:border-indigo-800/50 dark:hover:bg-indigo-900/50 transition-colors">
                                    <i class="fas fa-user-plus"></i> Assign
                                </button>
                            </div>
                        </form>
                    </div>

                </div>

                <!-- Main Communication Thread -->
                <div class="w-full space-y-6">
                    <div
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                        <div
                            class="p-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 shrink-0 text-xl font-bold">
                                    {{ substr($inbox->guardianProfile->user->first_name ?? 'G', 0, 1) }}
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $inbox->guardianProfile->user->first_name ?? 'Guardian' }}
                                        {{ $inbox->guardianProfile->user->last_name ?? '' }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Started on {{ $inbox->created_at->format('M j, Y \a\t g:i A') }}
                                    </p>
                                </div>
                            </div>
                            @php
                                $statusStyles = [
                                    'unread' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                    'read' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                    'assigned' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                                    'resolved' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                    'closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                ];
                            @endphp
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $statusStyles[$inbox->status] ?? $statusStyles['read'] }}">
                                {{ ucfirst($inbox->status) }}
                            </span>
                        </div>

                        <!-- Replies list -->
                        <div class="p-5 space-y-6 bg-gray-100 dark:bg-gray-900 shadow-inner">
                            @foreach($inbox->replies as $reply)
                                @php
                                    $isGuardian = $reply->sender_type === \App\Models\GuardianProfile::class;
                                @endphp
                                <div class="flex gap-4 {{ $isGuardian ? '' : 'flex-row-reverse' }}">
                                    <!-- Avatar -->
                                    <div
                                        class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 {{ $isGuardian ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                                        @if($isGuardian)
                                            <i class="fas fa-user-friends"></i>
                                        @else
                                            <i class="fas fa-school"></i>
                                        @endif
                                    </div>

                                    <!-- Message Bubble -->
                                    <div class="flex flex-col {{ $isGuardian ? 'items-start' : 'items-end' }} max-w-[80%]">
                                        <div
                                            class="flex items-baseline gap-2 mb-1 {{ $isGuardian ? '' : 'flex-row-reverse' }}">
                                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                @if($isGuardian)
                                                    {{ $reply->sender->user->first_name ?? 'Guardian' }}
                                                    {{ $reply->sender->user->last_name ?? '' }}
                                                @else
                                                    School Admin / Teacher
                                                @endif
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $reply->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                        <div
                                            class="p-4 rounded-2xl text-sm {{ $isGuardian ? 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 rounded-tl-none' : 'bg-blue-600 text-white rounded-tr-none shadow-md' }}">
                                            {!! nl2br(e($reply->body)) !!}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Reply Form -->
                        @if(!in_array($inbox->status, ['resolved', 'closed']))
                            <div class="p-5 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                                <form action="{{ route('inbox.reply', $inbox) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="body" class="sr-only">Your Reply</label>
                                        <textarea id="body" name="body" rows="3"
                                            class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="Type your reply here..." required></textarea>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit"
                                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-sm">
                                            <i class="fas fa-paper-plane"></i> Send Reply
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @else
                            <div
                                class="p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-500 dark:text-gray-400 gap-2">
                                <i class="fas fa-lock"></i>
                                <p class="text-sm font-medium">This thread is marked as {{ $inbox->status }} and cannot
                                    receive new replies.</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>