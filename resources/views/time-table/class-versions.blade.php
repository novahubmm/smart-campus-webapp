<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                <i class="fas fa-clock"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">@gradeName($class->grade?->level ?? 0)</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">@className($class->name, $class->grade?->level) - {{ __('time_table.Time-table Versions') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-back-link 
                :href="route('time-table.index')"
                :text="__('time_table.Back to Time-table')"
            />
            
            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800 dark:border-green-900/50 dark:bg-green-900/30 dark:text-green-100">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800 dark:border-red-900/50 dark:bg-red-900/30 dark:text-red-100">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Class Info & Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">@className($class->name, $class->grade?->level)</h3>
                        <div class="flex flex-wrap items-center gap-3 mt-2 text-sm text-gray-600 dark:text-gray-400">
                            @if($class->grade)
                                <span><i class="fas fa-layer-group mr-1"></i>@gradeName($class->grade->level)</span>
                            @endif
                            @if($class->teacher)
                                <span><i class="fas fa-user mr-1"></i>{{ $class->teacher->user?->name ?? __('time_table.No Teacher') }}</span>
                            @endif
                            <span><i class="fas fa-calendar-alt mr-1"></i>{{ $timetables->count() }} {{ __('time_table.versions') }}</span>
                        </div>
                    </div>
                    <button type="button" onclick="openTimetableEditorCreate()" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-lg shadow-sm transition-all">
                        <i class="fas fa-plus"></i>
                        <span>{{ __('time_table.Create New Version') }}</span>
                    </button>
                </div>
            </div>

            <!-- All Versions -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('time_table.All Versions') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('time_table.Manage multiple schedule versions for different periods') }}</p>
                </div>

                @if($timetables->count())
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($timetables as $timetable)
                            <div class="p-4 {{ $timetable->is_active ? 'bg-green-50/50 dark:bg-green-900/10' : '' }}">
                                <div class="flex flex-col md:flex-row md:items-center gap-4">
                                    <!-- Version Info -->
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            @if($timetable->is_active)
                                                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                                            @endif
                                            <h4 class="font-semibold text-gray-900 dark:text-white">{{ $timetable->display_name }}</h4>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">v{{ $timetable->version }}</span>
                                        </div>
                                        
                                        <div class="flex flex-wrap items-center gap-3 text-sm">
                                            @if($timetable->is_active)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200">
                                                    <i class="fas fa-check-circle mr-1"></i>{{ __('time_table.Active') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                                    <i class="fas fa-circle mr-1"></i>{{ __('time_table.Inactive') }}
                                                </span>
                                            @endif

                                            <span class="text-gray-500 dark:text-gray-400">
                                                <i class="fas fa-calendar mr-1"></i>{{ $timetable->updated_at?->format('M d, Y') }}
                                            </span>

                                            @if($timetable->periods->count())
                                                <span class="text-gray-500 dark:text-gray-400">
                                                    <i class="fas fa-th mr-1"></i>{{ $timetable->periods->count() }} {{ __('time_table.slots') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex flex-wrap items-center gap-2">
                                        <!-- View/Edit -->
                                        <button type="button" onclick="openTimetableEditorFor('{{ $timetable->id }}')" class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                            <i class="fas fa-eye"></i>
                                            <span>{{ __('time_table.View') }}</span>
                                        </button>

                                        <!-- Set Active -->
                                        @if(!$timetable->is_active)
                                            <form method="POST" action="{{ route('time-table.set-active', $timetable) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-purple-700 dark:text-purple-300 bg-purple-100 dark:bg-purple-900/40 rounded-lg hover:bg-purple-200 dark:hover:bg-purple-900/60 transition-colors">
                                                    <i class="fas fa-star"></i>
                                                    <span>{{ __('time_table.Set Active') }}</span>
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Duplicate -->
                                        <form method="POST" action="{{ route('time-table.duplicate', $timetable) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-blue-700 dark:text-blue-300 bg-blue-100 dark:bg-blue-900/40 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/60 transition-colors">
                                                <i class="fas fa-copy"></i>
                                                <span>{{ __('time_table.Duplicate') }}</span>
                                            </button>
                                        </form>

                                        <!-- Delete -->
                                        @if(!$timetable->is_active)
                                        <form method="POST" action="{{ route('time-table.destroy', $timetable) }}" class="inline" id="delete-form-{{ $timetable->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    onclick="confirmDelete('{{ $timetable->id }}', '{{ $timetable->display_name }}')"
                                                    class="inline-flex items-center justify-center w-9 h-9 text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/40 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/60 transition-colors">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @else
                                        <button type="button" disabled title="{{ __('time_table.Cannot delete active timetable. Deactivate it first.') }}" class="inline-flex items-center justify-center w-9 h-9 text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700 rounded-lg cursor-not-allowed opacity-50">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-12 text-center">
                        <i class="fas fa-calendar-times text-5xl text-gray-300 dark:text-gray-600 mb-4"></i>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('time_table.No time-table versions') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('time_table.Create your first time-table version for this class.') }}</p>
                        <button type="button" onclick="openTimetableEditorCreate()" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                            <i class="fas fa-plus"></i>
                            <span>{{ __('time_table.Create Time-table') }}</span>
                        </button>
                    </div>
                @endif
            </div>

            <!-- Period Switch Requests -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('time_table.Period Switch Requests') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('time_table.Temporary schedule changes for specific dates') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200">
                                {{ $switchRequests->count() }} {{ __('time_table.requests') }}
                            </span>
                            @if($activeTimetable)
                            <button type="button" onclick="openCreateSwitchRequestModal()" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                                <i class="fas fa-plus"></i>
                                <span>{{ __('time_table.Create Request') }}</span>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>

                @if($switchRequests->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">{{ __('time_table.Date') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">{{ __('time_table.Period') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">{{ __('time_table.From Teacher') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">{{ __('time_table.To Teacher') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">{{ __('time_table.Reason') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">{{ __('time_table.Status') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">{{ __('time_table.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($switchRequests as $request)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $request->date->format('M d, Y') }}</span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $request->date->format('l') }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="text-sm text-gray-900 dark:text-white">P{{ $request->period?->period_number ?? '-' }}</span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $request->period?->subject?->name ?? '-' }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="text-sm text-gray-900 dark:text-white">{{ $request->fromTeacher?->user?->name ?? '-' }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="text-sm text-gray-900 dark:text-white">{{ $request->toTeacher?->user?->name ?? '-' }}</span>
                                            @if($request->to_subject)
                                                <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $request->to_subject }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate block">{{ $request->reason ?? '-' }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if($request->status === 'accepted')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200">
                                                    <i class="fas fa-check-circle mr-1"></i>{{ __('time_table.Accepted') }}
                                                </span>
                                            @elseif($request->status === 'rejected')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200">
                                                    <i class="fas fa-times-circle mr-1"></i>{{ __('time_table.Rejected') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200">
                                                    <i class="fas fa-clock mr-1"></i>{{ __('time_table.Pending') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            @if($request->status === 'pending')
                                                <div class="flex items-center justify-end gap-1">
                                                    <form method="POST" action="{{ route('time-table.switch-request.approve', $request) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/40 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/60 transition-colors" title="{{ __('time_table.Approve') }}">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('time-table.switch-request.reject', $request) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/40 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/60 transition-colors" title="{{ __('time_table.Reject') }}">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-8 text-center">
                        <i class="fas fa-exchange-alt text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('time_table.No switch requests') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('time_table.Teachers can request temporary schedule changes from the mobile app') }}</p>
                    </div>
                @endif
            </div>

            <!-- Help Section -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-lightbulb text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">{{ __('time_table.Version Management Tips') }}</h4>
                        <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                            <li><i class="fas fa-check mr-2"></i>{{ __('time_table.Create multiple versions for different schedule periods (e.g., exam week, regular week)') }}</li>
                            <li><i class="fas fa-check mr-2"></i>{{ __('time_table.Set a version as Active to make it the current schedule') }}</li>
                            <li><i class="fas fa-check mr-2"></i>{{ __('time_table.Duplicate a version to quickly create variations') }}</li>
                            <li><i class="fas fa-check mr-2"></i>{{ __('time_table.Publish a version to make it visible to teachers and students') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Include the Timetable Editor Modal --}}
    @include('time-table._timetable-editor-modal', [
        'class' => $class,
        'subjects' => $subjects,
        'timetables' => $timetables,
        'timeFormat' => $timeFormat ?? '24h',
    ])

    {{-- Create Switch Request Modal --}}
    @if($activeTimetable)
    <div id="createSwitchRequestModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" onclick="closeCreateSwitchRequestModal()"></div>
            
            <div class="relative inline-block w-full max-w-lg p-6 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 rounded-xl shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('time_table.Create Switch Request') }}</h3>
                    <button type="button" onclick="closeCreateSwitchRequestModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('time-table.switch-request.store', $class) }}" id="switchRequestForm">
                    @csrf
                    
                    <!-- Period Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('time_table.Select Period') }} <span class="text-red-500">*</span></label>
                        <select name="period_id" id="switchPeriodSelect" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500" onchange="updatePeriodInfo()">
                            <option value="">-- {{ __('time_table.Select Period') }} --</option>
                            @foreach($activeTimetable->periods->sortBy(['day_of_week', 'period_number']) as $period)
                                <option value="{{ $period->id }}" 
                                    data-subject="{{ $period->subject?->name ?? 'N/A' }}"
                                    data-teacher="{{ $period->teacher?->user?->name ?? 'N/A' }}"
                                    data-teacher-id="{{ $period->teacher_profile_id }}"
                                    data-day="{{ ucfirst($period->day_of_week) }}">
                                    {{ ucfirst($period->day_of_week) }} - P{{ $period->period_number }} ({{ $period->subject?->name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Current Info (readonly) -->
                    <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ __('time_table.Current Schedule') }}</p>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('time_table.Subject') }}:</span>
                                <span id="currentSubject" class="ml-1 font-medium text-gray-900 dark:text-white">-</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('time_table.Teacher') }}:</span>
                                <span id="currentTeacher" class="ml-1 font-medium text-gray-900 dark:text-white">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Request Teacher -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('time_table.Request Teacher') }} <span class="text-red-500">*</span></label>
                        <select name="to_teacher_id" id="requestTeacherSelect" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500" onchange="updateTeacherSubjects()">
                            <option value="">-- {{ __('time_table.Select Teacher') }} --</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" data-subjects="{{ json_encode($teacher->subjects->pluck('id')) }}">{{ $teacher->user?->name ?? 'Unknown' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Request Subject -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('time_table.Request Subject') }} <span class="text-red-500">*</span></label>
                        <select name="to_subject_id" id="requestSubjectSelect" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500" disabled>
                            <option value="">-- {{ __('time_table.Select Teacher First') }} --</option>
                        </select>
                    </div>

                    <!-- Date -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('time_table.Date') }} <span class="text-red-500">*</span></label>
                        <input type="date" name="date" required min="{{ date('Y-m-d') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Reason -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('time_table.Reason') }}</label>
                        <textarea name="reason" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('time_table.Enter reason for switch request') }}"></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" onclick="closeCreateSwitchRequestModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            {{ __('time_table.Cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                            {{ __('time_table.Create Request') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // All subjects data for filtering
        const allSubjects = @json($subjects->map(fn($s) => ['id' => $s->id, 'name' => $s->name]));

        function openCreateSwitchRequestModal() {
            document.getElementById('createSwitchRequestModal').classList.remove('hidden');
        }

        function closeCreateSwitchRequestModal() {
            document.getElementById('createSwitchRequestModal').classList.add('hidden');
            document.getElementById('switchRequestForm').reset();
            document.getElementById('currentSubject').textContent = '-';
            document.getElementById('currentTeacher').textContent = '-';
            resetSubjectSelect();
        }

        function updatePeriodInfo() {
            const select = document.getElementById('switchPeriodSelect');
            const option = select.options[select.selectedIndex];
            
            if (option.value) {
                document.getElementById('currentSubject').textContent = option.dataset.subject;
                document.getElementById('currentTeacher').textContent = option.dataset.teacher;
            } else {
                document.getElementById('currentSubject').textContent = '-';
                document.getElementById('currentTeacher').textContent = '-';
            }
        }

        function updateTeacherSubjects() {
            const teacherSelect = document.getElementById('requestTeacherSelect');
            const subjectSelect = document.getElementById('requestSubjectSelect');
            const option = teacherSelect.options[teacherSelect.selectedIndex];
            
            // Clear current options
            subjectSelect.innerHTML = '';
            
            if (!option.value) {
                subjectSelect.innerHTML = '<option value="">-- {{ __('time_table.Select Teacher First') }} --</option>';
                subjectSelect.disabled = true;
                return;
            }

            // Get teacher's subjects
            const teacherSubjectIds = JSON.parse(option.dataset.subjects || '[]');
            
            // Filter subjects that belong to this teacher
            const teacherSubjects = allSubjects.filter(s => teacherSubjectIds.includes(s.id));
            
            if (teacherSubjects.length === 0) {
                subjectSelect.innerHTML = '<option value="">-- {{ __('time_table.No subjects for this teacher') }} --</option>';
                subjectSelect.disabled = true;
                return;
            }

            // Add default option
            subjectSelect.innerHTML = '<option value="">-- {{ __('time_table.Select Subject') }} --</option>';
            
            // Add teacher's subjects
            teacherSubjects.forEach(subject => {
                const opt = document.createElement('option');
                opt.value = subject.id;
                opt.textContent = subject.name;
                subjectSelect.appendChild(opt);
            });
            
            subjectSelect.disabled = false;
        }

        function resetSubjectSelect() {
            const subjectSelect = document.getElementById('requestSubjectSelect');
            subjectSelect.innerHTML = '<option value="">-- {{ __('time_table.Select Teacher First') }} --</option>';
            subjectSelect.disabled = true;
        }
    </script>
    @endif
</x-app-layout>
