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
        uploadFiles(ev) {
            const files = Array.from(ev.target.files);
            if (!files.length) return;
            const currentCount = {{ $event->attachments->count() }};
            const maxFiles = 30;
            if (currentCount + files.length > maxFiles) {
                alert('Maximum ' + maxFiles + ' files allowed. You can add ' + (maxFiles - currentCount) + ' more.');
                return;
            }
            for (const file of files) {
                if (file.size > 10 * 1024 * 1024) { alert(file.name + ': {{ __('events.File exceeds 10MB limit') }}'); return; }
            }
            this.uploading = true;
            const uploadNext = async (i) => {
                if (i >= files.length) { window.location.reload(); return; }
                const formData = new FormData();
                formData.append('file', files[i]);
                formData.append('_token', '{{ csrf_token() }}');
                await fetch('{{ route('events.upload', $event) }}', { method: 'POST', body: formData, headers: { 'Accept': 'application/json' } });
                uploadNext(i + 1);
            };
            uploadNext(0);
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

            <!-- Polls & Surveys Section (Always Visible) -->
            <div class="p-6 md:p-8 border-t border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-sm font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">
                        {{ __('events.Polls & Surveys') }}
                    </h4>
                    @if(($event->calculated_status === 'upcoming') && (auth()->id() === $event->organized_by || auth()->user()->hasRole('admin')))
                        <button @click="modals.poll = true"
                            class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-600 rounded-lg font-bold text-xs text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all shadow-sm">
                            <i class="fas fa-plus text-[10px]"></i> {{ __('events.Create Poll') }}
                        </button>
                    @endif
                </div>

                @if($event->polls->count() > 0)
                    @php
                        $isOrganizer = auth()->id() === $event->organized_by;
                        $isAdmin = auth()->user()->hasRole('admin');
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($event->polls as $poll)
                            <div
                                class="bg-gray-50 dark:bg-gray-900/40 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-semibold text-gray-900 dark:text-white">{{ $poll->question }}</h4>
                                        @if(!$poll->is_currently_active)
                                            <span
                                                class="text-[10px] font-bold text-red-500 bg-red-100 dark:bg-red-900/30 px-2 py-0.5 rounded uppercase">
                                                {{ $poll->expires_at?->isPast() ? __('events.Expired') : __('events.Closed') }}
                                            </span>
                                        @endif
                                    </div>
                                    @if(($isOrganizer || $isAdmin) && $poll->is_active)
                                        <button @click="togglePoll('{{ $poll->id }}')"
                                            class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded transition-colors text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                            {{ __('events.End Poll') }}
                                        </button>
                                    @endif
                                </div>

                                <div class="space-y-3">
                                    @foreach($poll->options as $option)
                                        @php
                                            $totalVotes = $poll->options->sum(fn($o) => $o->votes->count());
                                            $optionVotes = $option->votes->count();
                                            $percentage = $totalVotes > 0 ? ($optionVotes / $totalVotes) * 100 : 0;
                                            $hasVoted = $option->votes->contains('user_id', auth()->id());
                                            $pollHasVoted = $poll->options->flatMap->votes->contains('user_id', auth()->id());
                                            $showResults = $pollHasVoted || !$poll->is_currently_active || $isAdmin || $isOrganizer;
                                        @endphp
                                        <div class="relative">
                                            <button type="button"
                                                class="w-full relative flex items-center justify-between p-3 rounded-lg border text-sm transition-all overflow-hidden
                                                                                                    {{ $showResults ? 'cursor-default' : 'hover:border-indigo-400 cursor-pointer' }}
                                                                                                    {{ $hasVoted ? 'border-indigo-500 ring-1 ring-indigo-500 bg-indigo-50/50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800' }}"
                                                @disabled($showResults || !$poll->is_currently_active)
                                                @click="vote('{{ $option->id }}')">

                                                <div class="absolute left-0 top-0 bottom-0 bg-indigo-500/10 transition-all duration-1000"
                                                    style="width: {{ $showResults ? $percentage : 0 }}%"></div>

                                                <div class="relative flex items-center gap-3 z-10 w-full">
                                                    <div
                                                        class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0 {{ $hasVoted ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-300 dark:border-gray-600 text-transparent' }}">
                                                        <i class="fas fa-check text-[8px]"></i>
                                                    </div>
                                                    <span
                                                        class="font-medium text-gray-900 dark:text-white flex-1 text-left">{{ $option->option_text }}</span>
                                                    @if($showResults)
                                                        <span class="text-xs font-bold text-gray-500">{{ round($percentage) }}%</span>
                                                    @endif
                                                </div>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-4 flex items-center justify-between text-[10px] text-gray-500 font-medium">
                                    <span>{{ $totalVotes }} {{ __('events.Votes') }}</span>
                                    <span>{{ $poll->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-10 text-center">
                        <div
                            class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                            <i class="fas fa-poll text-xl text-gray-300 dark:text-gray-600"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-400 dark:text-gray-500">{{ __('events.No polls yet') }}</p>
                        <p class="text-xs text-gray-300 dark:text-gray-600 mt-1">
                            {{ __('events.Polls will appear here once created') }}
                        </p>
                    </div>
                @endif
            </div>

            <!-- RSVP Section -->
            @if(!auth()->user()->hasRole('admin'))
                <div class="border-t border-gray-200 dark:border-gray-700 p-6 md:p-8">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">
                        {{ __('events.Your Response') }}
                    </h4>
                    <div class="flex items-center gap-3 max-w-md">
                        <form action="{{ route('events.respond', $event) }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="status" value="going">
                            <button type="submit"
                                class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-lg border font-bold text-sm transition-all {{ $event->auth_response === 'going' ? 'bg-green-600 border-green-600 text-white shadow-md' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-500 hover:bg-green-50' }}">
                                <i class="fas fa-check-circle"></i>
                                {{ __('events.Going') }}
                            </button>
                        </form>
                        <form action="{{ route('events.respond', $event) }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="status" value="not_going">
                            <button type="submit"
                                class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-lg border font-bold text-sm transition-all {{ $event->auth_response === 'not_going' ? 'bg-red-600 border-red-600 text-white shadow-md' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-500 hover:bg-red-50' }}">
                                <i class="fas fa-times-circle"></i>
                                {{ __('events.Not Going') }}
                            </button>
                        </form>
                    </div>
                    <div class="mt-3 flex items-center gap-4 text-xs font-bold text-gray-500">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500"></span>
                            {{ $event->responses->where('status', 'going')->count() }}</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span>
                            {{ $event->responses->where('status', 'not_going')->count() }}</span>
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
                                <input type="file" class="hidden" multiple @change="uploadFiles($event)" accept="*/*">
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

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                    @forelse($event->attachments as $attachment)
                        <div
                            class="flex items-center gap-3 p-3 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 transition-all hover:shadow-md hover:border-gray-300 dark:hover:border-gray-500 group">
                            <div
                                class="shrink-0 w-10 h-10 rounded-lg bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 flex items-center justify-center text-gray-500 dark:text-gray-400 text-[10px] font-bold uppercase border border-gray-200 dark:border-gray-700">
                                {{ pathinfo($attachment->file_name, PATHINFO_EXTENSION) }}
                            </div>
                            <a href="{{ Storage::url($attachment->file_path) }}" download class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-gray-900 dark:text-white truncate">
                                    {{ $attachment->file_name }}
                                </p>
                                <p class="text-[10px] text-gray-500">
                                    {{ number_format($attachment->file_size / 1024, 0) }} KB
                                </p>
                            </a>
                            @if(auth()->id() === $attachment->uploaded_by || auth()->user()->hasRole('admin'))
                                <form action="{{ route('events.attachments.destroy', $attachment) }}" method="POST"
                                    onsubmit="return confirm('{{ __('events.Are you sure?') }}')"
                                    class="opacity-0 group-hover:opacity-100 transition-all">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-600 p-1">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </form>
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

            <!-- Create Poll Modal -->
            <template x-if="true">
                <div x-show="modals.poll" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
                    aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div x-show="modals.poll" x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500/75 transition-opacity"
                            @click="modals.poll = false"></div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                            aria-hidden="true">&#8203;</span>
                        <div x-show="modals.poll" x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                            <form action="{{ route('events.polls.store', $event) }}" method="POST">
                                @csrf
                                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                                        {{ __('events.Create New Poll') }}
                                    </h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label
                                                class="block text-xs font-bold text-gray-500 uppercase mb-1">{{ __('events.Question') }}</label>
                                            <input type="text" name="question"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                required>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-bold text-gray-500 uppercase mb-1">{{ __('events.Options') }}</label>
                                            <div class="space-y-2">
                                                <template x-for="(opt, index) in pollOptions" :key="index">
                                                    <div class="flex gap-2">
                                                        <input type="text" name="options[]"
                                                            class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                            :placeholder="'Option ' + (index + 1)" required>
                                                        <button type="button" x-show="pollOptions.length > 2"
                                                            @click="removeOption(index)"
                                                            class="text-gray-400 hover:text-red-500"><i
                                                                class="fas fa-trash-alt"></i></button>
                                                    </div>
                                                </template>
                                            </div>
                                            <button type="button" @click="addOption()"
                                                class="mt-3 text-xs font-bold text-indigo-600 hover:text-indigo-500"><i
                                                    class="fas fa-plus"></i>
                                                {{ __('events.Add Another Option') }}</button>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="bg-gray-50 dark:bg-gray-900/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                                    <button type="submit"
                                        class="w-full sm:w-auto inline-flex justify-center rounded-lg px-4 py-2 bg-indigo-600 text-white font-bold text-sm shadow-sm hover:bg-indigo-700 transition-all">{{ __('events.Create Poll') }}</button>
                                    <button type="button" @click="modals.poll = false"
                                        class="mt-3 sm:mt-0 w-full sm:w-auto inline-flex justify-center rounded-lg px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 font-bold text-sm shadow-sm hover:bg-gray-50">{{ __('events.Cancel') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </template>

        </div>


</x-app-layout>