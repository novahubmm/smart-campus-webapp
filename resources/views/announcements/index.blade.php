<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg">
                <i class="fas fa-bullhorn"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('announcements.Communications') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('announcements.Announcement Management') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10" x-data="announcementManager({
        announcements: @js($announcements->items() ? collect($announcements->items())->map(fn($a) => [
            'id' => $a->id,
            'title' => $a->title,
            'content' => $a->content,
            'announcement_type_id' => $a->announcement_type_id,
            'priority' => $a->priority,
            'location' => $a->location,
            'event_id' => $a->event_id,
            'target_roles' => $a->target_roles ?? [],
            'target_grades' => $a->target_grades ?? ['all'],
            'target_departments' => $a->target_departments ?? ['all'],
            'publish_date' => $a->publish_date?->format('Y-m-d H:i:s'),
            'is_published' => $a->is_published,
        ]) : []),
        announcementTypes: @js($announcementTypes),
        endpoints: {
            base: '{{ url('announcements') }}',
            store: '{{ route('announcements.store') }}',
        },
    })">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800 dark:border-green-900/50 dark:bg-green-900/30 dark:text-green-100">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <x-stat-card 
                    icon="fas fa-bullhorn"
                    :title="__('announcements.Total')"
                    :number="$stats['total'] ?? 0"
                    :subtitle="__('announcements.All announcements')"
                />
                <x-stat-card 
                    icon="fas fa-check-circle"
                    :title="__('announcements.Published')"
                    :number="$stats['published'] ?? 0"
                    :subtitle="__('announcements.Live now')"
                />
                <x-stat-card 
                    icon="fas fa-file-alt"
                    :title="__('announcements.Drafts')"
                    :number="$stats['draft'] ?? 0"
                    :subtitle="__('announcements.Pending')"
                />
                <x-stat-card 
                    icon="fas fa-exclamation-triangle"
                    :title="__('announcements.Urgent')"
                    :number="$stats['urgent'] ?? 0"
                    :subtitle="__('announcements.High priority')"
                />
            </div>

            <!-- Main Content -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('announcements.All Announcements') }}</h3>
                    <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-amber-600 hover:bg-amber-700" @click="openModal()">
                        <i class="fas fa-plus"></i>{{ __('announcements.Create Announcement') }}
                    </button>
                </div>

                <!-- Filter Bar -->
                <form method="GET" action="{{ route('announcements.index') }}" class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ __('announcements.Type') }}</label>
                            <select name="type" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-amber-500 focus:ring-amber-500">
                                <option value="">{{ __('announcements.All Types') }}</option>
                                @foreach($announcementTypes as $type)
                                    <option value="{{ $type->id }}" @selected(request('type') === $type->id)>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ __('announcements.Priority') }}</label>
                            <select name="priority" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-amber-500 focus:ring-amber-500">
                                <option value="">{{ __('announcements.All Priorities') }}</option>
                                @foreach($priorities as $priority)
                                    <option value="{{ $priority }}" @selected(request('priority') === $priority)>{{ ucfirst($priority) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ __('announcements.Status') }}</label>
                            <select name="status" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-amber-500 focus:ring-amber-500">
                                <option value="">{{ __('announcements.All') }}</option>
                                <option value="published" @selected(request('status') === 'published')>{{ __('announcements.Published') }}</option>
                                <option value="draft" @selected(request('status') === 'draft')>{{ __('announcements.Draft') }}</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ __('announcements.Target') }}</label>
                            <select name="target" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-amber-500 focus:ring-amber-500">
                                <option value="">{{ __('announcements.All Targets') }}</option>
                                <optgroup label="{{ __('announcements.Grades') }}">
                                    @foreach($grades as $grade)
                                        <option value="grade:{{ $grade->id }}" @selected(request('target') === 'grade:'.$grade->id)>{{ $grade->name }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="{{ __('announcements.Departments') }}">
                                    @foreach($departments as $department)
                                        <option value="dept:{{ $department->id }}" @selected(request('target') === 'dept:'.$department->id)>{{ $department->name }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 px-3 py-2 text-sm font-semibold rounded-lg text-white bg-gray-800 dark:bg-gray-700 hover:bg-gray-900 dark:hover:bg-gray-600">{{ __('announcements.Apply') }}</button>
                            <a href="{{ route('announcements.index') }}" class="px-3 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">{{ __('announcements.Reset') }}</a>
                        </div>
                    </div>
                </form>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('announcements.Type') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('announcements.Title') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('announcements.Priority') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('announcements.Participants') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('announcements.Target') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('announcements.Status') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('announcements.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($announcements as $announcement)
                                @php
                                    $typeData = $announcement->announcementType;
                                    $priorityStyles = [
                                        'urgent' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                        'high' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
                                        'medium' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                                        'low' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                    ];
                                @endphp
                                <tr>
                                    <td class="px-4 py-3">
                                        @if($typeData)
                                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-semibold" style="background-color: {{ $typeData->color }}20; color: {{ $typeData->color }}">
                                                <span class="w-4 h-4 flex-shrink-0">{!! $typeData->icon !!}</span>
                                                {{ $typeData->name }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-sm">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $announcement->title }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ Str::limit($announcement->content, 50) }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $priorityStyles[$announcement->priority] ?? $priorityStyles['low'] }}">
                                            {{ ucfirst($announcement->priority) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($announcement->target_roles ?? [] as $role)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                                    {{ ucfirst($role) }}
                                                </span>
                                            @endforeach
                                            @if(empty($announcement->target_roles))
                                                <span class="text-gray-400 text-xs">—</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @php
                                                $targetGrades = $announcement->target_grades ?? ['all'];
                                                $targetDepts = $announcement->target_departments ?? ['all'];
                                                $hasTeacherOrGuardian = collect($announcement->target_roles ?? [])->intersect(['teacher', 'guardian'])->isNotEmpty();
                                                $hasStaff = in_array('staff', $announcement->target_roles ?? []);
                                            @endphp
                                            @if($hasTeacherOrGuardian)
                                                @if(in_array('all', $targetGrades))
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                                        <i class="fas fa-graduation-cap mr-1 text-[10px]"></i>{{ __('announcements.All Grades') }}
                                                    </span>
                                                @else
                                                    @foreach($grades->whereIn('id', $targetGrades)->take(2) as $grade)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                                            {{ $grade->name }}
                                                        </span>
                                                    @endforeach
                                                    @if(count($targetGrades) > 2)
                                                        <span class="text-xs text-gray-500">+{{ count($targetGrades) - 2 }}</span>
                                                    @endif
                                                @endif
                                            @endif
                                            @if($hasStaff)
                                                @if(in_array('all', $targetDepts))
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                                                        <i class="fas fa-building mr-1 text-[10px]"></i>{{ __('announcements.All Departments') }}
                                                    </span>
                                                @else
                                                    @foreach($departments->whereIn('id', $targetDepts)->take(2) as $dept)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                                                            {{ Str::limit($dept->name, 10) }}
                                                        </span>
                                                    @endforeach
                                                    @if(count($targetDepts) > 2)
                                                        <span class="text-xs text-gray-500">+{{ count($targetDepts) - 2 }}</span>
                                                    @endif
                                                @endif
                                            @endif
                                            @if(!$hasTeacherOrGuardian && !$hasStaff)
                                                <span class="text-gray-400 text-xs">—</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($announcement->is_published)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                                {{ __('announcements.Published') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                {{ __('announcements.Draft') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('announcements.show', $announcement) }}" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-gray-500 flex items-center justify-center hover:border-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700" title="{{ __('announcements.View Details') }}">
                                                <i class="fas fa-eye text-xs"></i>
                                            </a>
                                            <button type="button" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-blue-500 flex items-center justify-center hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30" title="{{ __('announcements.Edit') }}" @click="openEditModal(@js($announcement))">
                                                <i class="fas fa-pen text-xs"></i>
                                            </button>
                                            <button type="button" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-red-500 flex items-center justify-center hover:border-red-400 hover:bg-red-50 dark:hover:bg-red-900/30" title="{{ __('announcements.Delete') }}" @click="submitDelete('{{ $announcement->id }}')">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                            <i class="fas fa-bullhorn text-4xl mb-3 opacity-50"></i>
                                            <p class="text-sm">{{ __('announcements.No announcements found') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <x-table-pagination :paginator="$announcements" />
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal()">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-3xl my-8 flex flex-col shadow-2xl max-h-[calc(100vh-4rem)]" @click.stop>
                    <form :action="formAction" method="POST" x-ref="announcementForm" class="flex flex-col min-h-0" @submit="handleFormSubmit">
                        @csrf
                        <template x-if="formMethod === 'PUT'"><input type="hidden" name="_method" value="PUT"></template>
                        
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-t-xl">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg">
                                    <i class="fas" :class="formMethod === 'PUT' ? 'fa-edit' : 'fa-plus'"></i>
                                </span>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="formMethod === 'PUT' ? '{{ __('announcements.Edit Announcement') }}' : '{{ __('announcements.Create New Announcement') }}'"></h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('announcements.Fill in the announcement details') }}</p>
                                </div>
                            </div>
                            <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" @click="closeModal()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <!-- Modal Body -->
                        <div class="flex-1 overflow-y-auto p-5 space-y-5">
                            <!-- Basic Info Section -->
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ __('announcements.Basic Information') }}</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('announcements.Title') }} <span class="text-red-500">*</span></label>
                                        <input type="text" name="title" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500" x-model="form.title" required placeholder="{{ __('announcements.Enter announcement title') }}">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('announcements.Type') }} <span class="text-red-500">*</span></label>
                                        <select name="announcement_type_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500" x-model="form.announcement_type_id" required>
                                            <option value="">{{ __('announcements.Select type') }}</option>
                                            @foreach($announcementTypes as $type)
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('announcements.Message') }} <span class="text-red-500">*</span></label>
                                    <textarea name="content" rows="4" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500" x-model="form.content" required placeholder="{{ __('announcements.Enter announcement message...') }}"></textarea>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('announcements.Priority') }} <span class="text-red-500">*</span></label>
                                        <select name="priority" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500" x-model="form.priority" required>
                                            <option value="low">{{ __('announcements.Low') }}</option>
                                            <option value="medium">{{ __('announcements.Medium') }}</option>
                                            <option value="high">{{ __('announcements.High') }}</option>
                                            <option value="urgent">{{ __('announcements.Urgent') }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('announcements.Location') }}</label>
                                        <input type="text" name="location" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500" x-model="form.location" placeholder="{{ __('announcements.e.g., Main Campus') }}">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('announcements.Link Event') }}</label>
                                        <select name="event_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500" x-model="form.event_id">
                                            <option value="">{{ __('announcements.No event') }}</option>
                                            @foreach($events as $event)
                                                <option value="{{ $event->id }}">{{ $event->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Participants Section -->
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <i class="fas fa-users text-amber-500"></i>{{ __('announcements.Participants') }}
                                </h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ __('announcements.Select who should receive this announcement and push notification') }}</p>
                                
                                <!-- Role Checkboxes -->
                                <div class="flex flex-wrap gap-4 mb-4">
                                    @foreach($participantRoles as $role)
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="target_roles[]" value="{{ $role }}" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-700" x-model="form.target_roles" :checked="form.target_roles.includes('{{ $role }}')">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ ucfirst($role) }}</span>
                                            @if($role === 'guardian')
                                                <span class="text-xs text-gray-400">(FCM)</span>
                                            @elseif($role === 'teacher')
                                                <span class="text-xs text-gray-400">(FCM)</span>
                                            @elseif($role === 'staff')
                                                <span class="text-xs text-gray-400">(Web Push)</span>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>

                                <!-- Grade Selection for Teacher/Guardian -->
                                <div x-show="form.target_roles.includes('teacher') || form.target_roles.includes('guardian')" x-collapse class="mt-4 p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                                        <i class="fas fa-graduation-cap text-amber-500 mr-1"></i>
                                        {{ __('announcements.Select Grades') }}
                                        <span class="text-xs text-gray-400 font-normal ml-1">(for Teacher & Guardian)</span>
                                    </label>
                                    <div class="flex flex-wrap gap-2">
                                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border cursor-pointer transition-all"
                                               :class="form.target_grades.length === 0 || form.target_grades.includes('all') ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300' : 'border-gray-300 dark:border-gray-600 hover:border-amber-300'">
                                            <input type="checkbox" value="all" class="hidden" x-model="form.target_grades" @change="if(form.target_grades.includes('all')) form.target_grades = ['all']">
                                            <span class="text-sm font-medium">{{ __('announcements.All Grades') }}</span>
                                        </label>
                                        @foreach($grades as $grade)
                                            <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border cursor-pointer transition-all"
                                                   :class="form.target_grades.includes('{{ $grade->id }}') ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300' : 'border-gray-300 dark:border-gray-600 hover:border-amber-300'">
                                                <input type="checkbox" name="target_grades[]" value="{{ $grade->id }}" class="hidden" x-model="form.target_grades" @change="form.target_grades = form.target_grades.filter(g => g !== 'all')">
                                                <span class="text-sm font-medium">{{ $grade->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <input type="hidden" name="target_grades_json" :value="JSON.stringify(form.target_grades)">
                                </div>

                                <!-- Department Selection for Staff -->
                                <div x-show="form.target_roles.includes('staff')" x-collapse class="mt-4 p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                                        <i class="fas fa-building text-amber-500 mr-1"></i>
                                        {{ __('announcements.Select Departments') }}
                                        <span class="text-xs text-gray-400 font-normal ml-1">(for Staff)</span>
                                    </label>
                                    <div class="flex flex-wrap gap-2">
                                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border cursor-pointer transition-all"
                                               :class="form.target_departments.length === 0 || form.target_departments.includes('all') ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300' : 'border-gray-300 dark:border-gray-600 hover:border-amber-300'">
                                            <input type="checkbox" value="all" class="hidden" x-model="form.target_departments" @change="if(form.target_departments.includes('all')) form.target_departments = ['all']">
                                            <span class="text-sm font-medium">{{ __('announcements.All Departments') }}</span>
                                        </label>
                                        @foreach($departments as $department)
                                            <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border cursor-pointer transition-all"
                                                   :class="form.target_departments.includes('{{ $department->id }}') ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300' : 'border-gray-300 dark:border-gray-600 hover:border-amber-300'">
                                                <input type="checkbox" name="target_departments[]" value="{{ $department->id }}" class="hidden" x-model="form.target_departments" @change="form.target_departments = form.target_departments.filter(d => d !== 'all')">
                                                <span class="text-sm font-medium">{{ $department->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <input type="hidden" name="target_departments_json" :value="JSON.stringify(form.target_departments)">
                                </div>
                            </div>

                            <!-- Publishing Section -->
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <i class="fas fa-paper-plane text-amber-500"></i>{{ __('announcements.Publishing Options') }}
                                </h4>
                                <div class="grid grid-cols-2 gap-3 mb-4">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="publish_mode" value="now" class="hidden peer" x-model="publishMode">
                                        <div class="p-3 border-2 rounded-lg text-center transition-all peer-checked:border-amber-500 peer-checked:bg-amber-50 dark:peer-checked:bg-amber-900/20 border-gray-200 dark:border-gray-600 hover:border-amber-300">
                                            <i class="fas fa-paper-plane text-xl mb-1 text-gray-400"></i>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('announcements.Publish Now') }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('announcements.Send immediately') }}</p>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="publish_mode" value="schedule" class="hidden peer" x-model="publishMode">
                                        <div class="p-3 border-2 rounded-lg text-center transition-all peer-checked:border-amber-500 peer-checked:bg-amber-50 dark:peer-checked:bg-amber-900/20 border-gray-200 dark:border-gray-600 hover:border-amber-300">
                                            <i class="fas fa-clock text-xl mb-1 text-gray-400"></i>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('announcements.Schedule') }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('announcements.Set date & time') }}</p>
                                        </div>
                                    </label>
                                </div>
                                <div x-show="publishMode === 'schedule'" x-collapse class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('announcements.Publish Date') }}</label>
                                        <input type="date" name="publish_date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500" x-model="form.publish_date">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('announcements.Publish Time') }}</label>
                                        <input type="time" name="publish_time" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500" x-model="form.publish_time">
                                    </div>
                                </div>
                                <input type="hidden" name="is_published" :value="publishMode === 'now' ? 1 : 0">
                                <input type="hidden" name="type" value="general">
                            </div>
                        </div>
                        
                        <!-- Modal Footer -->
                        <div class="flex items-center justify-between gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl flex-wrap">
                            <button type="button" class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="closeModal()">
                                {{ __('announcements.Cancel') }}
                            </button>
                            <div class="flex items-center gap-3">
                                <button x-show="publishMode === 'schedule'" type="button" class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="saveDraft()">
                                    <i class="fas fa-save mr-2"></i>{{ __('announcements.Save Draft') }}
                                </button>
                                <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-amber-600 hover:bg-amber-700" 
                                        x-bind:disabled="isSubmitting">
                                    <template x-if="isSubmitting">
                                        <i class="fas fa-spinner fa-spin mr-2"></i>
                                    </template>
                                    <template x-if="!isSubmitting">
                                        <i class="fas fa-paper-plane mr-2"></i>
                                    </template>
                                    <span x-text="isSubmitting ? '{{ __('announcements.Publishing...') }}' : (publishMode === 'now' ? '{{ __('announcements.Publish Now') }}' : '{{ __('announcements.Schedule') }}')"></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function announcementManager(options) {
            return {
                showModal: false,
                formMethod: 'POST',
                formAction: options.endpoints.store,
                publishMode: 'now',
                isSubmitting: false,
                announcementTypes: options.announcementTypes || [],
                form: {
                    title: '',
                    content: '',
                    announcement_type_id: '',
                    priority: 'medium',
                    location: '',
                    event_id: '',
                    target_roles: [],
                    target_grades: ['all'],
                    target_departments: ['all'],
                    publish_date: '',
                    publish_time: ''
                },
                openModal() {
                    this.showModal = true;
                    this.formMethod = 'POST';
                    this.formAction = options.endpoints.store;
                    this.publishMode = 'now';
                    this.isSubmitting = false;
                    this.form = this.defaultForm();
                },
                openEditModal(announcement) {
                    this.showModal = true;
                    this.formMethod = 'PUT';
                    this.formAction = options.endpoints.base + '/' + announcement.id;
                    this.publishMode = announcement.is_published ? 'now' : 'schedule';
                    this.isSubmitting = false;
                    this.form = {
                        title: announcement.title || '',
                        content: announcement.content || '',
                        announcement_type_id: announcement.announcement_type_id || '',
                        priority: announcement.priority || 'medium',
                        location: announcement.location || '',
                        event_id: announcement.event_id || '',
                        target_roles: announcement.target_roles || [],
                        target_grades: announcement.target_grades || ['all'],
                        target_departments: announcement.target_departments || ['all'],
                        publish_date: announcement.publish_date ? announcement.publish_date.split(' ')[0] : '',
                        publish_time: announcement.publish_date ? announcement.publish_date.split(' ')[1]?.substring(0, 5) : ''
                    };
                },
                closeModal() {
                    this.showModal = false;
                    this.isSubmitting = false;
                    this.form = this.defaultForm();
                },
                saveDraft() {
                    const form = this.$refs.announcementForm;
                    const isPublishedInput = form.querySelector('input[name="is_published"]');
                    if (isPublishedInput) isPublishedInput.value = '0';
                    this.isSubmitting = true;
                    
                    // Add event listener for form submission completion
                    form.addEventListener('submit', () => {
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    });
                    
                    form.submit();
                },
                handleFormSubmit(event) {
                    // Don't prevent default - let the form submit normally
                    this.isSubmitting = true;
                },
                submitDelete(id) {
                    this.$dispatch('confirm-show', {
                        title: '{{ __('announcements.Delete Announcement') }}',
                        message: '{{ __('announcements.confirm_delete') }}',
                        confirmText: '{{ __('announcements.Delete') }}',
                        cancelText: '{{ __('announcements.Cancel') }}',
                        onConfirm: () => {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = options.endpoints.base + '/' + id;
                            form.innerHTML = `@csrf <input type="hidden" name="_method" value="DELETE">`;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                },
                defaultForm() {
                    return {
                        title: '',
                        content: '',
                        announcement_type_id: '',
                        priority: 'medium',
                        location: '',
                        event_id: '',
                        target_roles: [],
                        target_grades: ['all'],
                        target_departments: ['all'],
                        publish_date: '',
                        publish_time: ''
                    };
                }
            };
        }
    </script>

    <style>[x-cloak] { display: none !important; }</style>
</x-app-layout>
