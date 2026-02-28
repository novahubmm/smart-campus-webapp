@php
    $categoryColors = [
        'academic' => '#4285f4',
        'sports' => '#34a853',
        'cultural' => '#fbbc04',
        'meeting' => '#ea4335',
        'holiday' => '#9e9e9e',
        'exam' => '#9c27b0',
        'other' => '#607d8b',
    ];
    $catColor = $event->category?->color ?? ($categoryColors[$event->type] ?? '#6b7280');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span
                class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-lg">
                <i class="fas fa-calendar-alt"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('events.Event Management') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('events.Event Details') }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6" x-data="{
        modals: { poll: false },
        pollOptions: ['', ''],
        addOption() { this.pollOptions.push(''); },
        removeOption(index) { this.pollOptions.splice(index, 1); },
        uploading: false,
        imageModal: { show: false, url: '', name: '', id: '', canDelete: false },
        uploadFiles(ev) {
            const files = Array.from(ev.target.files);
            if (!files.length) return;
            const currentCount = {{ $event->attachments->count() }};
            const maxFiles = 30;
            
            // Check total file limit
            if (currentCount + files.length > maxFiles) {
                showToast('{{ __('events.Maximum 30 files allowed') }}. {{ __('events.You can add') }} ' + (maxFiles - currentCount) + ' {{ __('events.more') }}.', 'error');
                return;
            }
            
            // Smart batch limit based on file sizes
            const maxSize3MB = 3 * 1024 * 1024; // 3MB
            const maxSize6MB = 6 * 1024 * 1024; // 6MB
            let hasLargeFiles = false;
            
            // Validate file types and size
            for (const file of files) {
                if (!['image/png', 'image/jpeg', 'image/jpg'].includes(file.type)) {
                    showToast(file.name + ': {{ __('events.Only PNG and JPG images are allowed') }}', 'error');
                    return;
                }
                if (file.size > maxSize6MB) {
                    showToast(file.name + ': {{ __('events.File exceeds 6MB limit') }}', 'error');
                    return;
                }
                if (file.size > maxSize3MB) {
                    hasLargeFiles = true;
                }
            }
            
            // Dynamic batch limit: 3 files if any file > 3MB, otherwise 5 files
            const maxFilesPerBatch = hasLargeFiles ? 3 : 5;
            
            if (files.length > maxFilesPerBatch) {
                if (hasLargeFiles) {
                    showToast('{{ __('events.For files over 3MB, maximum 3 files per upload') }}', 'error');
                } else {
                    showToast('{{ __('events.Maximum 5 files per upload') }}. {{ __('events.You can upload multiple times to reach 30 total') }}.', 'error');
                }
                return;
            }
            
            this.uploading = true;
            const uploadNext = async (i) => {
                if (i >= files.length) { window.location.reload(); return; }
                const formData = new FormData();
                formData.append('file', files[i]);
                formData.append('_token', '{{ csrf_token() }}');
                
                try {
                    const response = await fetch('{{ route('events.upload', $event) }}', { 
                        method: 'POST', 
                        body: formData, 
                        headers: { 'Accept': 'application/json' } 
                    });
                    
                    if (!response.ok) {
                        const data = await response.json();
                        showToast(data.message || '{{ __('events.Upload failed') }}', 'error');
                        this.uploading = false;
                        return;
                    }
                    
                    uploadNext(i + 1);
                } catch (error) {
                    showToast('{{ __('events.Upload failed') }}', 'error');
                    this.uploading = false;
                }
            };
            uploadNext(0);
        },
        deleteAttachment(attachmentId, fileName) {
            // Use the same confirm dialog as logout
            this.$dispatch('confirm-show', {
                title: '{{ __('events.Delete Image') }}',
                message: '{{ __('events.Are you sure you want to delete this image?') }}',
                confirmText: '{{ __('events.Delete') }}',
                cancelText: '{{ __('events.Cancel') }}',
                onConfirm: () => {
                    fetch(`/events/attachments/${attachmentId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('{{ __('events.Image deleted successfully') }}', 'success');
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            showToast(data.message || '{{ __('events.Failed to delete image') }}', 'error');
                        }
                    })
                    .catch(error => {
                        showToast('{{ __('events.Failed to delete image') }}', 'error');
                    });
                }
            });
        },
        showImageModal(url, name) {
            this.imageModal = { show: true, url: url, name: name };
        },
        closeImageModal() {
            this.imageModal = { show: false, url: '', name: '', id: '', canDelete: false };
        },
        vote(optionId) {
            fetch('/polls/' + optionId + '/vote', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } }).then(r => r.ok ? window.location.reload() : r.json().then(d => alert(d.message)));
        },
        togglePoll(pollId) {
            if (!confirm('{{ __('events.Are you sure?') }}')) return;
            fetch('/polls/' + pollId + '/toggle', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } }).then(r => r.ok ? window.location.reload() : r.json().then(d => alert(d.message)));
        }
    }" @open-poll-modal.window="modals.poll = true">
        <!-- Back Button & Actions -->
        <div class="flex items-center justify-between gap-4">
            @php
                $backRoute = route('events.index');
                $backLabel = __('events.Back to Events');
                if (auth()->user()->hasRole('guardian')) {
                    $backRoute = route('guardian.announcements', ['tab' => 'events']);
                    $backLabel = __('events.Back to Announcements');
                } elseif (auth()->user()->hasRole('student')) {
                    $backRoute = route('student.events-announcements');
                    $backLabel = __('events.Back to Events & Announcements');
                }
            @endphp
            <a href="{{ $backRoute }}"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left"></i>
                {{ $backLabel }}
            </a>


        </div>

        <!-- Main Event Card -->
        <div
            class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden text-gray-900 dark:text-gray-100">
            <!-- Header Section -->
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                    <div class="flex-1 space-y-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 shadow-sm">
                                @if($event->category?->icon)
                                    <i class="{{ $event->category->icon }}"></i>
                                @else
                                    <i class="fas fa-calendar-check"></i>
                                @endif
                                {{ $event->category?->name ?? ucfirst($event->type) }}
                            </span>

                            <span @php $status = $event->calculated_status; @endphp
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $status === 'upcoming' ? 'bg-blue-100 text-blue-700 border-blue-200' : ($status === 'completed' ? 'bg-gray-100 text-gray-700 border-gray-200' : 'bg-green-100 text-green-700 border-green-200') }} border shadow-sm">
                                <i
                                    class="fas {{ $status === 'upcoming' ? 'fa-clock' : ($status === 'completed' ? 'fa-check-circle' : 'fa-play-circle') }}"></i>
                                {{ __('events.' . ucfirst($status)) }}
                            </span>
                        </div>

                        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white leading-tight">
                            {{ $event->title }}
                        </h1>

                        <div
                            class="flex flex-wrap items-center gap-4 text-sm font-medium text-gray-500 dark:text-gray-400">
                            @if($event->venue)
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-map-marker-alt text-red-500"></i>
                                    <span>{{ $event->venue }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Organizer Info -->
                    <div
                        class="flex items-center gap-3 bg-white dark:bg-gray-900/50 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                        <div
                            class="h-10 w-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-lg">
                            {{ substr($event->organizer->name ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider font-bold">
                                {{ __('events.Organized by') }}
                            </p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">
                                {{ $event->organizer->name ?? __('events.Unknown') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description Section -->
            <div class="p-6 md:p-8">
                <div class="prose prose-indigo dark:prose-invert max-w-none">
                    <h4 class="text-sm font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest mb-4">
                        {{ __('events.About this Event') }}
                    </h4>
                    <div class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                        {!! nl2br(e($event->description ?? __('events.No description provided'))) !!}
                    </div>
                </div>
            </div>

            {{-- Target Audience Details Section --}}
            @php
                $evTargetRoles = $event->target_roles ?? [];
                $evHasTeacher = in_array('teacher', $evTargetRoles);
                $evHasGuardian = in_array('guardian', $evTargetRoles);
                $evHasStaff = in_array('staff', $evTargetRoles);

                $evTeacherGrades = collect($event->target_teacher_grades ?? $event->target_grades ?? ['all'])->map(fn($v) => (string) $v)->all();
                $evGuardianGrades = collect($event->target_guardian_grades ?? $event->target_grades ?? ['all'])->map(fn($v) => (string) $v)->all();
                $evDepts = collect($event->target_departments ?? ['all'])->map(fn($v) => (string) $v)->all();
                $showAudienceDetails = $evHasTeacher || $evHasGuardian || $evHasStaff;
            @endphp
            @if($showAudienceDetails)
                <div class="px-6 md:px-8 pb-6 border-t border-gray-100 dark:border-gray-700 pt-6">
                    <h4 class="text-sm font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest mb-4">
                        {{ __('events.Audience') }}
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        {{-- Teacher Grades --}}
                        @if($evHasTeacher)
                            <div
                                class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-100 dark:border-blue-800">
                                <p
                                    class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                    <i class="fas fa-chalkboard-teacher"></i> {{ __('events.Teachers') }}
                                </p>
                                <div class="flex flex-wrap gap-1.5">
                                    @if(in_array('all', $evTeacherGrades))
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-800 dark:text-blue-200">
                                            {{ __('events.All Grades') }}
                                        </span>
                                    @else
                                        @foreach($grades as $grade)
                                            @if(in_array((string) $grade->id, $evTeacherGrades))
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-800 dark:text-blue-200">
                                                    {{ $grade->name }}
                                                </span>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Guardian Grades --}}
                        @if($evHasGuardian)
                            <div
                                class="bg-green-50 dark:bg-green-900/20 rounded-xl p-4 border border-green-100 dark:border-green-800">
                                <p
                                    class="text-xs font-bold text-green-600 dark:text-green-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                    <i class="fas fa-user-friends"></i> {{ __('events.Guardians') }}
                                </p>
                                <div class="flex flex-wrap gap-1.5">
                                    @if(in_array('all', $evGuardianGrades))
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-800 dark:text-green-200">
                                            {{ __('events.All Grades') }}
                                        </span>
                                    @else
                                        @foreach($grades as $grade)
                                            @if(in_array((string) $grade->id, $evGuardianGrades))
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-800 dark:text-green-200">
                                                    {{ $grade->name }}
                                                </span>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Staff Departments --}}
                        @if($evHasStaff)
                            <div
                                class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-4 border border-purple-100 dark:border-purple-800">
                                <p
                                    class="text-xs font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                    <i class="fas fa-user-tie"></i> {{ __('events.Staff') }}
                                </p>
                                <div class="flex flex-wrap gap-1.5">
                                    @if(in_array('all', $evDepts))
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-700 dark:bg-purple-800 dark:text-purple-200">
                                            {{ __('events.All Departments') }}
                                        </span>
                                    @else
                                        @foreach($departments as $dept)
                                            @if(in_array((string) $dept->id, $evDepts))
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-700 dark:bg-purple-800 dark:text-purple-200">
                                                    {{ $dept->name }}
                                                </span>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Event Schedule Breakdown -->
            @php
                $scheduleList = $event->schedule_list;
                $isMultiDay = count($scheduleList) > 1;
            @endphp
            @if(!empty($scheduleList))
                <div class="p-6 md:p-8 border-t border-gray-100 dark:border-gray-700">
                    <h4 class="text-sm font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest mb-6">
                        {{ __('events.Full Schedule') }}
                    </h4>
                    <div class="space-y-4">
                        @foreach($scheduleList as $day)
                            <div
                                class="flex flex-col md:flex-row md:items-center gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700">
                                <div class="flex-shrink-0 flex items-center gap-3">
                                    <div
                                        class="w-12 h-12 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 flex flex-col items-center justify-center text-indigo-700 dark:text-indigo-300">
                                        <span
                                            class="text-[10px] uppercase font-bold leading-tight">{{ \Carbon\Carbon::parse($day['date'])->format('M') }}</span>
                                        <span
                                            class="text-lg font-extrabold leading-tight">{{ \Carbon\Carbon::parse($day['date'])->format('d') }}</span>
                                    </div>
                                    <div class="min-w-[100px]">
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">
                                            {{ $isMultiDay ? __('events.Day') . ' ' . ($loop->index + 1) : __('events.Event Day') }}
                                        </p>
                                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($day['date'])->format('l') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex-1 md:border-l md:border-gray-200 md:dark:border-gray-700 md:pl-6">
                                    <div class="flex flex-wrap items-center gap-4">
                                        <div
                                            class="flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            <i class="fas fa-clock text-indigo-400"></i>
                                            {{ $day['start_time'] }} - {{ $day['end_time'] }}
                                        </div>
                                        @if(!empty($day['description']))
                                            <div
                                                class="flex items-center gap-1.5 text-sm font-bold text-indigo-600 dark:text-indigo-400">
                                                <i class="fas fa-info-circle"></i>
                                                {{ $day['description'] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- RSVP Section -->
            @if(auth()->user()->hasRole('admin'))
                <!-- Admin View: Show Response Statistics -->
                <div class="border-t border-gray-200 dark:border-gray-700 p-6 md:p-8">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">
                        {{ __('events.Response Statistics') }}
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Going -->
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="flex items-center gap-2 text-green-700 dark:text-green-400 mb-1">
                                        <i class="fas fa-check-circle text-lg"></i>
                                        <span class="text-sm font-bold">{{ __('events.Going') }}</span>
                                    </div>
                                    <div class="text-3xl font-bold text-green-600 dark:text-green-400">
                                        {{ $event->responses->where('status', 'going')->count() }}
                                    </div>
                                </div>
                                <div class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
                                    <i class="fas fa-user-check text-green-600 dark:text-green-400 text-xl"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Not Sure -->
                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="flex items-center gap-2 text-amber-700 dark:text-amber-400 mb-1">
                                        <i class="fas fa-question-circle text-lg"></i>
                                        <span class="text-sm font-bold">{{ __('events.Not Sure') }}</span>
                                    </div>
                                    <div class="text-3xl font-bold text-amber-600 dark:text-amber-400">
                                        {{ $event->responses->where('status', 'maybe')->count() }}
                                    </div>
                                </div>
                                <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center">
                                    <i class="fas fa-user-clock text-amber-600 dark:text-amber-400 text-xl"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Not Going -->
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="flex items-center gap-2 text-red-700 dark:text-red-400 mb-1">
                                        <i class="fas fa-times-circle text-lg"></i>
                                        <span class="text-sm font-bold">{{ __('events.Not Going') }}</span>
                                    </div>
                                    <div class="text-3xl font-bold text-red-600 dark:text-red-400">
                                        {{ $event->responses->where('status', 'not_going')->count() }}
                                    </div>
                                </div>
                                <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center">
                                    <i class="fas fa-user-times text-red-600 dark:text-red-400 text-xl"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Responses -->
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400 font-semibold">{{ __('events.Total Responses') }}</span>
                            <span class="text-gray-900 dark:text-white font-bold text-lg">{{ $event->responses->count() }}</span>
                        </div>
                    </div>
                </div>
            @else
                <!-- User View: Response Buttons -->
                <div class="border-t border-gray-200 dark:border-gray-700 p-6 md:p-8">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">
                        {{ __('events.Your Response') }}
                    </h4>
                    <div class="flex items-center gap-3">
                        <form action="{{ route('events.respond', $event) }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="status" value="going">
                            <button type="submit"
                                class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-lg border font-bold text-sm transition-all {{ $event->auth_response === 'going' ? 'bg-green-600 border-green-600 text-white shadow-md' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-500 hover:bg-green-50 dark:hover:bg-green-900/20' }}">
                                <i class="fas fa-check-circle"></i>
                                {{ __('events.Going') }}
                            </button>
                        </form>
                        <form action="{{ route('events.respond', $event) }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="status" value="maybe">
                            <button type="submit"
                                class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-lg border font-bold text-sm transition-all {{ $event->auth_response === 'maybe' ? 'bg-amber-600 border-amber-600 text-white shadow-md' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-500 hover:bg-amber-50 dark:hover:bg-amber-900/20' }}">
                                <i class="fas fa-question-circle"></i>
                                {{ __('events.Not Sure') }}
                            </button>
                        </form>
                        <form action="{{ route('events.respond', $event) }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="status" value="not_going">
                            <button type="submit"
                                class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-lg border font-bold text-sm transition-all {{ $event->auth_response === 'not_going' ? 'bg-red-600 border-red-600 text-white shadow-md' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-500 hover:bg-red-50 dark:hover:bg-red-900/20' }}">
                                <i class="fas fa-times-circle"></i>
                                {{ __('events.Not Going') }}
                            </button>
                        </form>
                    </div>
                    <div class="mt-3 flex items-center gap-4 text-xs font-bold text-gray-500 dark:text-gray-400">
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            {{ $event->responses->where('status', 'going')->count() }}
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            {{ $event->responses->where('status', 'maybe')->count() }}
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            {{ $event->responses->where('status', 'not_going')->count() }}
                        </span>
                    </div>
                </div>
            @endif

            <!-- Attachments Section (Full Width) -->
            <div class="border-t border-gray-200 dark:border-gray-700 p-6 md:p-8">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                            {{ __('events.Attachments') }}
                        </h4>
                        <span
                            class="text-[10px] font-bold text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">
                            {{ $event->attachments->count() }} / 30
                        </span>
                    </div>
                    @if(auth()->id() === $event->organized_by || auth()->user()->hasRole('admin'))
                        @if($event->attachments->count() < 30)
                            <label
                                class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-600 transition-all shadow-sm">
                                <i class="fas fa-cloud-upload-alt text-indigo-500"></i>
                                <span
                                    class="text-sm font-bold text-gray-700 dark:text-gray-200">{{ __('events.Choose Files') }}</span>
                                <input type="file" class="hidden" multiple @change="uploadFiles($event)" accept="image/png,image/jpeg,image/jpg" max="30">
                            </label>
                        @endif
                    @endif
                </div>

                {{-- Upload Progress --}}
                <div x-show="uploading" x-cloak class="mb-4">
                    <div
                        class="flex items-center gap-3 p-3 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800">
                        <svg class="animate-spin h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span
                            class="text-sm font-medium text-indigo-700 dark:text-indigo-300">{{ __('events.Uploading files...') }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
                    @forelse($event->attachments as $attachment)
                        <div class="relative group rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all cursor-pointer"
                             @click="imageModal = { 
                                 show: true, 
                                 url: '{{ Storage::url($attachment->file_path) }}', 
                                 name: '{{ $attachment->file_name }}',
                                 id: '{{ $attachment->id }}',
                                 canDelete: {{ (auth()->id() === $attachment->uploaded_by || auth()->user()->hasRole('admin')) ? 'true' : 'false' }}
                             }">
                            {{-- Image Preview --}}
                            <div class="block aspect-square">
                                <img src="{{ Storage::url($attachment->file_path) }}" 
                                     alt="{{ $attachment->file_name }}"
                                     class="w-full h-full object-cover">
                            </div>
                            
                            {{-- File Info Overlay --}}
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-2">
                                <p class="text-xs font-bold text-white truncate">
                                    {{ $attachment->file_name }}
                                </p>
                                <p class="text-[10px] text-gray-300">
                                    {{ number_format($attachment->file_size / 1024, 0) }} KB
                                </p>
                            </div>
                            
                            {{-- Delete Button --}}
                            @if(auth()->id() === $attachment->uploaded_by || auth()->user()->hasRole('admin'))
                                <button @click.stop="deleteAttachment('{{ $attachment->id }}', '{{ $attachment->file_name }}')"
                                    class="absolute top-2 right-2 w-8 h-8 rounded-full bg-red-500 hover:bg-red-600 text-white shadow-lg opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center z-10">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            @endif
                        </div>
                    @empty
                        <div class="col-span-full flex flex-col items-center justify-center py-8 text-center">
                            <div
                                class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-3">
                                <i class="fas fa-paperclip text-lg text-gray-300 dark:text-gray-600"></i>
                            </div>
                            <p class="text-sm font-medium text-gray-400 dark:text-gray-500">
                                {{ __('events.No attachments yet') }}
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Image Modal -->
            <div x-show="imageModal.show" 
                 x-cloak
                 @click="closeImageModal()"
                 @keydown.escape.window="closeImageModal()"
                 class="fixed inset-0 z-50 bg-black/95 flex items-center justify-center"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                
                <!-- Top Bar with Delete and Close (Fixed Position) -->
                <div class="fixed top-0 left-0 right-0 flex items-center justify-between p-4 bg-gradient-to-b from-black/50 to-transparent z-20">
                    <!-- Delete Button (Left) -->
                    <button x-show="imageModal.canDelete"
                            @click.stop="deleteAttachment(imageModal.id, imageModal.name); closeImageModal();" 
                            class="w-12 h-12 rounded-full bg-red-500 hover:bg-red-600 text-white flex items-center justify-center transition-all shadow-lg">
                        <i class="fas fa-trash-alt text-lg"></i>
                    </button>
                    <div x-show="!imageModal.canDelete"></div>
                    
                    <!-- Close Button (Right) -->
                    <button @click="closeImageModal()" 
                            class="w-12 h-12 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition-all backdrop-blur-sm">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>

                <!-- Image Container (Full Screen) -->
                <div @click.stop class="w-full h-full flex items-center justify-center p-4">
                    <img :src="imageModal.url" 
                         :alt="imageModal.name"
                         class="max-w-full max-h-full object-contain"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100">
                </div>
                
                <!-- Bottom Bar with Filename (Fixed Position) -->
                <div class="fixed bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black/50 to-transparent z-20">
                    <p class="text-white font-medium text-center text-sm" x-text="imageModal.name"></p>
                </div>
            </div>

        </div>


</x-app-layout>