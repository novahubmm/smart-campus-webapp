@php
    // Determine initial tab from explicit tab parameter (set by pagination links)
    $initialTab = request('tab', 'batches');
    $showGradesFirst = !$batches->count() && $grades->count();
@endphp

<!-- Select2 CSS -->
<link href="/css/select2.min.css" rel="stylesheet" />

<x-app-layout>
    <x-slot name="styles">
        <link rel="stylesheet" href="{{ asset('css/academic-management.css') }}?v={{ time() }}">
    </x-slot>
    <x-slot name="header">
        <x-page-header
            icon="fas fa-graduation-cap"
            iconBg="bg-blue-50 dark:bg-blue-900/30"
            iconColor="text-blue-700 dark:text-blue-200"
            :subtitle="__('academic_management.Academic Setup')"
            :title="__('academic_management.Academic Management')"
        />
    </x-slot>
    

    <div class="py-6 sm:py-10" x-data="academicManagementState(@js($initialTab))">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div x-show="submitting" x-transition.opacity x-cloak class="fixed inset-0 z-40 flex items-center justify-center bg-black/40">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 shadow-lg flex items-center gap-3">
                    <span class="w-5 h-5 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></span>
                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ __('academic_management.Updating results...') }}</span>
                </div>
            </div>

            <!-- Academic Stats Cards -->
            <div class="stats-grid-secondary vertical-stats tab-aligned-stats">
                <x-stat-card 
                    icon="fas fa-calendar-alt"
                    :title="__('academic_management.Active Batches')"
                    :number="$batchesCount"
                    :subtitle="__('academic_management.Academic cycles')"
                />
                <x-stat-card 
                    icon="fas fa-layer-group"
                    :title="__('academic_management.Total Grades')"
                    :number="$gradesCount"
                    :subtitle="__('academic_management.Grade levels')"
                />
                <x-stat-card 
                    icon="fas fa-chalkboard"
                    :title="__('academic_management.Total Classes')"
                    :number="$classesCount"
                    :subtitle="__('academic_management.Active schedules')"
                />
                <x-stat-card 
                    icon="fas fa-door-open"
                    :title="__('academic_management.Total Rooms')"
                    :number="$roomsCount"
                    :subtitle="__('academic_management.Across all blocks')"
                />
                <x-stat-card 
                    icon="fas fa-book"
                    :title="__('academic_management.Total Subjects')"
                    :number="$subjectsCount"
                    :subtitle="__('academic_management.All departments')"
                />
            </div>

            <!-- Academic Structure Management Tabs -->
            <x-academic-tabs 
                :tabs="[
                    'batches' => __('academic_management.Batches'),
                    'grades' => __('academic_management.Grades'),
                    'classes' => __('academic_management.Classes'),
                    'rooms' => __('academic_management.Rooms'),
                    'subjects' => __('academic_management.Subjects'),
                ]"
                active-tab="tab"
            />

            <!-- Tab Content Container -->
            <div class="tab-content-container">
                <!-- Batches Tab Content -->
                <div x-show="tab === 'batches'" x-cloak class="tab-content" :class="tab === 'batches' ? 'active' : ''" id="batches-content">
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('academic_management.Batch Management') }}</h3>
                            <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700" onclick="openModal('batchModal')">
                                <i class="fas fa-plus"></i>{{ __('academic_management.Add Batch') }}
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Batch Name') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Status') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Students') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Start Date') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.End Date') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($batches as $batch)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <a href="{{ route('academic-management.batches.show', $batch->id) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">{{ $batch->name }}</a>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100">{{ __('academic_management.Active') }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $batch->students_count ?? 0 }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ optional($batch->start_date)->format('Y-m-d') ?? '—' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ optional($batch->end_date)->format('Y-m-d') ?? '—' }}</td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center justify-end gap-1">
                                                    <a href="{{ route('academic-management.batches.show', $batch->id) }}" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-blue-500 flex items-center justify-center hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30" title="{{ __('academic_management.View') }}">
                                                        <i class="fas fa-eye text-xs"></i>
                                                    </a>
                                                    <a href="#" 
                                                       @click.prevent="$dispatch('confirm-show', {
                                                           title: '{{ __('academic_management.Delete Batch') }}',
                                                           message: '{{ __('academic_management.confirm_delete') }}',
                                                           confirmText: '{{ __('academic_management.Delete') }}',
                                                           cancelText: '{{ __('academic_management.Cancel') }}',
                                                           onConfirm: () => document.getElementById('delete-batch-{{ $batch->id }}').submit()
                                                       })"
                                                       class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-red-500 flex items-center justify-center hover:border-red-400 hover:bg-red-50 dark:hover:bg-red-900/30" 
                                                       title="{{ __('academic_management.Delete') }}">
                                                        <i class="fas fa-trash text-xs"></i>
                                                    </a>
                                                    <form id="delete-batch-{{ $batch->id }}" method="POST" action="{{ route('academic-management.batches.destroy', $batch->id) }}" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('academic_management.No batches found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if(method_exists($batches, 'links'))
                            <x-table-pagination :paginator="$batches" tabParam="batches" />
                        @endif
                    </div>
                </div>

                <!-- Grades Tab Content -->
                <div x-show="tab === 'grades'" x-cloak class="tab-content" :class="tab === 'grades' ? 'active' : ''" id="grades-content">
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('academic_management.Grade Management') }}</h3>
                            <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700" onclick="openModal('gradeModal')">
                                <i class="fas fa-plus"></i>{{ __('academic_management.Add Grade') }}
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Grade') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Category') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Classes') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Students') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($grades as $grade)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <a href="{{ route('academic-management.grades.show', $grade->id) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">@gradeName($grade->level)</a>
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($grade->gradeCategory)
                                                    @php($categoryColor = $grade->gradeCategory->color ?? '#16a34a')
                                                    <span class="inline-flex items-center gap-2 px-2 py-1 rounded-full text-xs font-semibold" style="background-color: {{ $categoryColor }}1A; color: {{ $categoryColor }};">
                                                        <span class="w-2 h-2 rounded-full" style="background-color: {{ $categoryColor }}"></span>
                                                        {{ $grade->gradeCategory->name }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $grade->classes_count ?? 0 }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $grade->students_count ?? 0 }}</td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center justify-end gap-1">
                                                    <a href="{{ route('academic-management.grades.show', $grade->id) }}" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-blue-500 flex items-center justify-center hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30" title="{{ __('academic_management.View Details') }}">
                                                        <i class="fas fa-eye text-xs"></i>
                                                    </a>
                                                    <a href="#" 
                                                       @click.prevent="$dispatch('confirm-show', {
                                                           title: '{{ __('academic_management.Delete Grade') }}',
                                                           message: '{{ __('academic_management.confirm_delete') }}',
                                                           confirmText: '{{ __('academic_management.Delete') }}',
                                                           cancelText: '{{ __('academic_management.Cancel') }}',
                                                           onConfirm: () => document.getElementById('delete-grade-{{ $grade->id }}').submit()
                                                       })"
                                                       class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-red-500 flex items-center justify-center hover:border-red-400 hover:bg-red-50 dark:hover:bg-red-900/30" 
                                                       title="{{ __('academic_management.Delete Grade') }}">
                                                        <i class="fas fa-trash text-xs"></i>
                                                    </a>
                                                    <form id="delete-grade-{{ $grade->id }}" method="POST" action="{{ route('academic-management.grades.destroy', $grade->id) }}" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('academic_management.No grades found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if(method_exists($grades, 'links'))
                            <x-table-pagination :paginator="$grades" tabParam="grades" />
                        @endif
                    </div>
                </div>

                <!-- Classes Tab Content -->
                <div x-show="tab === 'classes'" x-cloak class="tab-content" :class="tab === 'classes' ? 'active' : ''" id="classes-content">
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('academic_management.Class Management') }}</h3>
                            <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700" onclick="openModal('classModal')">
                                <i class="fas fa-plus"></i>{{ __('academic_management.Add Class') }}
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Class Name') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Grade') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Room') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Students') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Class Teacher') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($classes as $class)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <a href="{{ route('academic-management.classes.show', $class->id) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">{{ \App\Helpers\SectionHelper::formatFullClassName($class->name, $class->grade?->level) }}</a>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                                @if($class->grade)
                                                    <a href="{{ route('academic-management.grades.show', $class->grade->id) }}" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">{{ $class->grade->name }}</a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                                @if($class->room)
                                                    <a href="{{ route('academic-management.rooms.show', $class->room->id) }}" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">{{ $class->room->name }}</a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $class->students_count ?? 0 }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $class->teacher?->user?->name ?? '—' }}</td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center justify-end gap-1">
                                                    <a href="{{ route('academic-management.classes.show', $class->id) }}" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-blue-500 flex items-center justify-center hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30" title="{{ __('academic_management.View Details') }}">
                                                        <i class="fas fa-eye text-xs"></i>
                                                    </a>
                                                    <a href="#" 
                                                       @click.prevent="$dispatch('confirm-show', {
                                                           title: '{{ __('academic_management.Delete Class') }}',
                                                           message: '{{ __('academic_management.confirm_delete') }}',
                                                           confirmText: '{{ __('academic_management.Delete') }}',
                                                           cancelText: '{{ __('academic_management.Cancel') }}',
                                                           onConfirm: () => document.getElementById('delete-class-{{ $class->id }}').submit()
                                                       })"
                                                       class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-red-500 flex items-center justify-center hover:border-red-400 hover:bg-red-50 dark:hover:bg-red-900/30" 
                                                       title="{{ __('academic_management.Delete Class') }}">
                                                        <i class="fas fa-trash text-xs"></i>
                                                    </a>
                                                    <form id="delete-class-{{ $class->id }}" method="POST" action="{{ route('academic-management.classes.destroy', $class->id) }}" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('academic_management.No classes found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if(method_exists($classes, 'links'))
                            <x-table-pagination :paginator="$classes" tabParam="classes" />
                        @endif
                    </div>
                </div>

                <!-- Rooms Tab Content -->
                <div x-show="tab === 'rooms'" x-cloak class="tab-content" :class="tab === 'rooms' ? 'active' : ''" id="rooms-content">
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('academic_management.Room Management') }}</h3>
                            <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700" onclick="openModal('roomModal')">
                                <i class="fas fa-plus"></i>{{ __('academic_management.Add Room') }}
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Room Number') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Room Name') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Location') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Class') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Status') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($rooms as $room)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <a href="{{ route('academic-management.rooms.show', $room->id) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">{{ $room->name }}</a>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $room->name ?? '—' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $room->building ?? 'Building A' }} · {{ $room->floor ?? '1st Floor' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                                @if($room->classes->isNotEmpty())
                                                    @foreach($room->classes as $class)
                                                        <a href="{{ route('academic-management.classes.show', $class->id) }}" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">{{ $class->name }}</a>@if(!$loop->last), @endif
                                                    @endforeach
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $room->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">{{ ucfirst($room->status ?? 'Available') }}</span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center justify-end gap-1">
                                                    <a href="{{ route('academic-management.rooms.show', $room->id) }}" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-blue-500 flex items-center justify-center hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30" title="{{ __('academic_management.View Details') }}">
                                                        <i class="fas fa-eye text-xs"></i>
                                                    </a>
                                                    <a href="#" 
                                                       @click.prevent="$dispatch('confirm-show', {
                                                           title: '{{ __('academic_management.Delete Room') }}',
                                                           message: '{{ __('academic_management.confirm_delete') }}',
                                                           confirmText: '{{ __('academic_management.Delete') }}',
                                                           cancelText: '{{ __('academic_management.Cancel') }}',
                                                           onConfirm: () => document.getElementById('delete-room-{{ $room->id }}').submit()
                                                       })"
                                                       class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-red-500 flex items-center justify-center hover:border-red-400 hover:bg-red-50 dark:hover:bg-red-900/30" 
                                                       title="{{ __('academic_management.Delete Room') }}">
                                                        <i class="fas fa-trash text-xs"></i>
                                                    </a>
                                                    <form id="delete-room-{{ $room->id }}" method="POST" action="{{ route('academic-management.rooms.destroy', $room->id) }}" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('academic_management.No rooms found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if(method_exists($rooms, 'links'))
                            <x-table-pagination :paginator="$rooms" tabParam="rooms" />
                        @endif
                    </div>
                </div>

                <!-- Subjects Tab Content -->
                <div x-show="tab === 'subjects'" x-cloak class="tab-content" :class="tab === 'subjects' ? 'active' : ''" id="subjects-content">
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('academic_management.Subject Management') }}</h3>
                            <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700" onclick="openModal('subjectModal')">
                                <i class="fas fa-plus"></i>{{ __('academic_management.Add Subject') }}
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Subject Code') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Subject Name') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Type') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Grade') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Teachers') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($subjects as $subject)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <a href="{{ route('academic-management.subjects.show', $subject->id) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">{{ $subject->code }}</a>
                                            </td>
                                            <td class="px-4 py-3">
                                                <a href="{{ route('academic-management.subjects.show', $subject->id) }}" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">{{ $subject->name }}</a>
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($subject->subjectType && strtolower($subject->subjectType->name) === 'core')
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-100">{{ $subject->subjectType->name }}</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-100">{{ $subject->subjectType->name ?? '—' }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                                @if($subject->grades->isNotEmpty())
                                                    {{ $subject->grades->map(fn($g) => __('grades.grade_' . $g->level))->implode(', ') }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $subject->teachers_count ?? 0 }}</td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center justify-end gap-1">
                                                    <a href="{{ route('academic-management.subjects.show', $subject->id) }}" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-blue-500 flex items-center justify-center hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30" title="{{ __('academic_management.View Details') }}">
                                                        <i class="fas fa-eye text-xs"></i>
                                                    </a>
                                                    <a href="#" 
                                                       @click.prevent="$dispatch('confirm-show', {
                                                           title: '{{ __('academic_management.Delete Subject') }}',
                                                           message: '{{ __('academic_management.confirm_delete') }}',
                                                           confirmText: '{{ __('academic_management.Delete') }}',
                                                           cancelText: '{{ __('academic_management.Cancel') }}',
                                                           onConfirm: () => document.getElementById('delete-subject-{{ $subject->id }}').submit()
                                                       })"
                                                       class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-red-500 flex items-center justify-center hover:border-red-400 hover:bg-red-50 dark:hover:bg-red-900/30" 
                                                       title="{{ __('academic_management.Delete Subject') }}">
                                                        <i class="fas fa-trash text-xs"></i>
                                                    </a>
                                                    <form id="delete-subject-{{ $subject->id }}" method="POST" action="{{ route('academic-management.subjects.destroy', $subject->id) }}" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('academic_management.No subjects found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if(method_exists($subjects, 'links'))
                            <x-table-pagination :paginator="$subjects" tabParam="subjects" />
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Modal -->
    <x-form-modal 
        id="batchModal" 
        title="{{ __('academic_management.Create Batch') }}" 
        icon="fas fa-folder-plus"
        action="{{ route('academic-management.batches.store') }}"
        :submitText="__('academic_management.Save Batch')"
        :cancelText="__('academic_management.Cancel')">
        @include('academic.partials.batch-form-fields')
    </x-form-modal>

    <!-- Grade Modal -->
    <x-form-modal 
        id="gradeModal" 
        title="{{ __('academic_management.Create Grade') }}" 
        icon="fas fa-layer-group"
        action="{{ route('academic-management.grades.store') }}"
        :submitText="__('academic_management.Save Grade')"
        :cancelText="__('academic_management.Cancel')">
        @include('academic.partials.grade-form-fields', [
            'batches' => $activeBatches,
            'gradeCategories' => $gradeCategories,
        ])
    </x-form-modal>

    <!-- Class Modal -->
    <x-form-modal 
        id="classModal" 
        title="{{ __('academic_management.Create Class') }}" 
        icon="fas fa-chalkboard"
        action="{{ route('academic-management.classes.store') }}"
        :submitText="__('academic_management.Save Class')"
        :cancelText="__('academic_management.Cancel')">
        @include('academic.partials.class-form-fields', [
            'grades' => $grades,
            'rooms' => $rooms,
            'teachers' => $teachers,
        ])
    </x-form-modal>

    <!-- Room Modal -->
    <x-form-modal 
        id="roomModal" 
        title="{{ __('academic_management.Create Room') }}" 
        icon="fas fa-door-open"
        action="{{ route('academic-management.rooms.store') }}"
        :submitText="__('academic_management.Save Room')"
        :cancelText="__('academic_management.Cancel')">
        @include('academic.partials.room-form-fields')
    </x-form-modal>

    <!-- Subject Modal -->
    <x-form-modal 
        id="subjectModal" 
        title="{{ __('academic_management.Create Subject') }}" 
        icon="fas fa-book"
        action="{{ route('academic-management.subjects.store') }}"
        :submitText="__('academic_management.Save Subject')"
        :cancelText="__('academic_management.Cancel')">
        
        <div class="space-y-1">
            <label for="subjectCode" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
                {{ __('academic_management.Subject Code') }} <span class="text-red-500">*</span>
            </label>
            <input 
                type="text" 
                id="subjectCode" 
                name="code" 
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" 
                placeholder="{{ __('academic_management.e.g., MATH101') }}"
                value="{{ old('code') }}"
                required>
            @error('code')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="space-y-1">
            <label for="subjectName" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
                {{ __('academic_management.Subject Name') }} <span class="text-red-500">*</span>
            </label>
            <input 
                type="text" 
                id="subjectName" 
                name="name" 
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" 
                placeholder="{{ __('academic_management.e.g., Mathematics') }}"
                value="{{ old('name') }}"
                required>
            @error('name')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="space-y-1">
                <label for="subjectIcon" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Icon</label>
                <input
                    type="text"
                    id="subjectIcon"
                    name="icon"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
                    value="{{ old('icon') }}"
                    placeholder="fas fa-book">
                @error('icon')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="space-y-1">
                <label for="subjectIconColor" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Icon Color</label>
                <input
                    type="text"
                    id="subjectIconColor"
                    name="icon_color"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
                    value="{{ old('icon_color') }}"
                    placeholder="#3B82F6">
                @error('icon_color')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="space-y-1">
                <label for="subjectProgressColor" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Progress Color</label>
                <input
                    type="text"
                    id="subjectProgressColor"
                    name="progress_color"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
                    value="{{ old('progress_color') }}"
                    placeholder="#22C55E">
                @error('progress_color')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <div class="space-y-1">
            <label for="createSubjectGrades" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
                {{ __('academic_management.Grades') }} <span class="text-red-500">*</span>
            </label>
            <select 
                id="createSubjectGrades" 
                name="grade_ids[]" 
                multiple
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
                required>
                @foreach($grades as $grade)
                    <option value="{{ $grade->id }}">@gradeName($grade->level)</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                <i class="fas fa-info-circle mr-1"></i>{{ __('academic_management.Select multiple grades that will teach this subject') }}
            </p>
            @error('grade_ids')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="space-y-1">
            <label for="subjectCategory" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
                {{ __('academic_management.Category') }}
            </label>
            <select 
                id="subjectCategory" 
                name="category" 
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500">
                <option value="core" {{ old('category') == 'core' ? 'selected' : '' }}>{{ __('academic_management.Core') }}</option>
                <option value="elective" {{ old('category') == 'elective' ? 'selected' : '' }}>{{ __('academic_management.Elective') }}</option>
            </select>
            @error('category')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </x-form-modal>

    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('js/academic-management.js') }}"></script>
    <script>
        function academicManagementState(initialTab) {
            return {
                tab: initialTab || 'batches',
                submitting: false,
                submitFilters(form) {
                    this.submitting = true;
                    form.submit();
                },
                init() {
                    // Watch for tab changes and update URL
                    this.$watch('tab', (value) => {
                        const url = new URL(window.location);
                        url.searchParams.set('tab', value);
                        window.history.pushState({}, '', url);
                    });
                }
            };
        }

        // Initialize Select2 for create subject modal
        $(document).ready(function() {
            // Store the original openModal function
            const originalOpenModal = window.openModal;
            
            // Override openModal to initialize Select2 when modal opens
            window.openModal = function(modalId) {
                // Call original function first
                originalOpenModal(modalId);
                
                // If it's the create subject modal, initialize Select2
                if (modalId === 'subjectModal') {
                    setTimeout(function() {
                        // Destroy existing Select2 if any
                        if ($('#createSubjectGrades').hasClass('select2-hidden-accessible')) {
                            $('#createSubjectGrades').select2('destroy');
                        }
                        
                        try {
                            // Initialize Select2
                            $('#createSubjectGrades').select2({
                                width: 'resolve',
                                placeholder: '{{ __('academic_management.Select grades') }}',
                                allowClear: true,
                                dropdownParent: $('#subjectModal')
                            });
                        } catch (error) {
                            // Silent fail
                        }
                    }, 150);
                }
            };
        });
    </script>
</x-app-layout>
