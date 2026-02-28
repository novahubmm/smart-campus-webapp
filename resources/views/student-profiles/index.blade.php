<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                <i class="fas fa-user-graduate"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('student_profiles.Profiles') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('student_profiles.Student Profiles') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden" x-data="{
        selectedGrade: '{{ request('grade') }}',
        selectedClass: '{{ request('class') }}',
        allClasses: @js($classes->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'grade_id' => $c->grade_id])),
        get filteredClasses() {
            if (!this.selectedGrade) {
                return [];
            }
            return this.allClasses.filter(c => c.grade_id == this.selectedGrade);
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-stat-card 
                    icon="fas fa-user-graduate"
                    :title="__('student_profiles.Total Active Students')"
                    :number="number_format($totals['active'])"
                    :subtitle="__('student_profiles.All enrolled')"
                />
                <x-stat-card 
                    icon="fas fa-user-plus"
                    :title="__('student_profiles.Total Students')"
                    :number="number_format($totals['all'])"
                    :subtitle="__('student_profiles.All profiles')"
                />
            </div>

            @php
                $tableColumns = [
                    [
                        'label' => __('student_profiles.Student ID'),
                        'render' => fn($student) => '<p class="text-sm font-mono font-semibold text-gray-900 dark:text-white">' . ($student->student_identifier ?? '—') . '</p>',
                    ],
                    [
                        'label' => __('student_profiles.Full Name'),
                        'render' => function($student) {
                            return '<p class="text-sm font-semibold text-gray-900 dark:text-white">' . e($student->user?->name ?? '—') . '</p>' .
                                   '<p class="text-xs text-gray-500 dark:text-gray-400">' . e($student->formatted_class_name) . '</p>';
                        },
                    ],
                    [
                        'label' => __('student_profiles.Age'),
                        'render' => function($student) {
                            $age = $student->dob ? now()->diff($student->dob) : null;
                            $ageDisplay = $age ? $age->y . ' year ' . $age->m . ' Month' : '—';
                            return '<span class="text-sm text-gray-700 dark:text-gray-300">' . $ageDisplay . '</span>';
                        },
                    ],
                    [
                        'label' => __('student_profiles.Parent Name'),
                        'render' => function($student) {
                            // Try direct fields first, then guardian relationship
                            $parentName = $student->father_name ?? $student->mother_name;
                            if (!$parentName && $student->guardians->isNotEmpty()) {
                                $parentName = $student->guardians->first()->user->name;
                            }
                            return '<span class="text-sm text-gray-700 dark:text-gray-300">' . e($parentName ?? '—') . '</span>';
                        },
                    ],
                    [
                        'label' => __('student_profiles.Phone'),
                        'render' => function($student) {
                            // Try direct fields first, then guardian relationship
                            $phone = $student->father_phone_no ?? $student->mother_phone_no ?? $student->emergency_contact_phone_no;
                            if (!$phone && $student->guardians->isNotEmpty()) {
                                $phone = $student->guardians->first()->user->phone;
                            }
                            return '<span class="text-sm text-gray-700 dark:text-gray-300">' . e($phone ?? '—') . '</span>';
                        },
                    ],
                    [
                        'label' => __('student_profiles.Enrollment Date'),
                        'render' => fn($student) => '<span class="text-sm text-gray-700 dark:text-gray-300">' . ($student->date_of_joining?->format('Y-m-d') ?? '—') . '</span>',
                    ],
                    [
                        'label' => __('student_profiles.Status'),
                        'render' => function($student) {
                            $statusClass = $student->status === 'active' 
                                ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100'
                                : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-100';
                            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold ' . $statusClass . '">' . ucfirst($student->status ?? 'active') . '</span>';
                        },
                    ],
                ];

                $tableActions = [];
                
                if (auth()->user()->can('manage student profiles')) {
                    $tableActions[] = [
                        'type' => 'link',
                        'url' => fn($student) => route('student-profiles.show', $student),
                        'icon' => 'fas fa-eye',
                        'title' => __('student_profiles.View Details'),
                    ];
                    
                    $tableActions[] = [
                        'type' => 'link',
                        'url' => fn($student) => route('student-profiles.edit', $student),
                        'icon' => 'fas fa-pen',
                        'title' => __('student_profiles.Edit'),
                    ];
                    
                    $tableActions[] = [
                        'type' => 'button',
                        'onclick' => fn($student) => "toggleStatus('{$student->id}', '" . ($student->status === 'active' ? 'inactive' : 'active') . "')",
                        'icon' => fn($student) => $student->status === 'active' ? 'fas fa-toggle-on' : 'fas fa-toggle-off',
                        'title' => fn($student) => $student->status === 'active' ? __('student_profiles.Deactivate') : __('student_profiles.Activate'),
                        'color' => fn($student) => $student->status === 'active' ? 'text-green-600 hover:text-green-700' : 'text-gray-400 hover:text-gray-500',
                    ];
                }
            @endphp

            <!-- Custom Table with Dynamic Class Filter -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <!-- Section Header -->
                <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('student_profiles.All Students') }}</h3>
                    @can('manage student profiles')
                        <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700" onclick="window.location.href='{{ route('student-profiles.create') }}'">
                            <i class="fas fa-plus"></i>{{ __('student_profiles.Add New Student') }}
                        </button>
                    @endcan
                </div>

                <!-- Filters -->
                <form method="GET" class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                        <!-- Grade Filter -->
                        <div class="flex flex-col gap-1">
                            <label for="grade" class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ __('student_profiles.Grade') }}</label>
                            <select 
                                id="grade" 
                                name="grade" 
                                x-model="selectedGrade"
                                @change="selectedClass = ''"
                                class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">{{ __('student_profiles.All Grades') }}</option>
                                @foreach($grades as $grade)
                                    <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Class Filter (Dynamic based on Grade) -->
                        <div class="flex flex-col gap-1">
                            <label for="class" class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ __('student_profiles.Class') }}</label>
                            <select 
                                id="class" 
                                name="class" 
                                x-model="selectedClass"
                                :disabled="!selectedGrade"
                                class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <option value="">{{ __('student_profiles.All Classes') }}</option>
                                <template x-for="classItem in filteredClasses" :key="classItem.id">
                                    <option :value="classItem.id" :selected="classItem.id == selectedClass" x-text="classItem.name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="flex flex-col gap-1">
                            <label for="status" class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ __('student_profiles.Status') }}</label>
                            <select 
                                id="status" 
                                name="status" 
                                class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">{{ __('student_profiles.All Status') }}</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('student_profiles.Active') }}</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('student_profiles.Inactive') }}</option>
                            </select>
                        </div>

                        <!-- Search Filter -->
                        <div class="flex flex-col gap-1">
                            <label for="search" class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ __('student_profiles.Search') }}</label>
                            <input 
                                type="text" 
                                id="search" 
                                name="search" 
                                class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="{{ __('student_profiles.Search by name, email, phone, student ID...') }}"
                                value="{{ request('search') }}"
                            >
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 px-3 py-2 text-sm font-semibold rounded-lg text-white bg-gray-800 dark:bg-gray-700 hover:bg-gray-900 dark:hover:bg-gray-600">{{ __('components.Apply') }}</button>
                            <a href="{{ route('student-profiles.index') }}" class="px-3 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">{{ __('components.Reset') }}</a>
                        </div>
                    </div>
                </form>

                <!-- Table -->
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    @foreach($tableColumns as $column)
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                            {{ $column['label'] }}
                                        </th>
                                    @endforeach
                                    @if(count($tableActions) > 0)
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('components.Actions') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($students as $student)
                                    <tr>
                                        @foreach($tableColumns as $column)
                                            <td class="px-4 py-3">
                                                {!! $column['render']($student) !!}
                                            </td>
                                        @endforeach
                                        @if(count($tableActions) > 0)
                                            <td class="px-4 py-3">
                                                <div class="flex items-center justify-end gap-1">
                                                    @foreach($tableActions as $action)
                                                        @if($action['type'] === 'link')
                                                            <a href="{{ $action['url']($student) }}" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-gray-500 flex items-center justify-center hover:border-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700" title="{{ $action['title'] }}">
                                                                <i class="{{ $action['icon'] }} text-xs"></i>
                                                            </a>
                                                        @elseif($action['type'] === 'button')
                                                            <button type="button" onclick="{{ $action['onclick']($student) }}" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 flex items-center justify-center hover:border-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 {{ is_callable($action['color']) ? $action['color']($student) : $action['color'] }}" title="{{ is_callable($action['title']) ? $action['title']($student) : $action['title'] }}">
                                                                <i class="{{ is_callable($action['icon']) ? $action['icon']($student) : $action['icon'] }} text-xs"></i>
                                                            </button>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($tableColumns) + (count($tableActions) > 0 ? 1 : 0) }}" class="px-4 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                                <i class="fas fa-user-graduate text-4xl mb-3 opacity-50"></i>
                                                <p class="text-sm">{{ __('student_profiles.No student profiles found') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <x-table-pagination :paginator="$students" />
            </div>
        </div>
    </div>

    <script>
        function toggleStatus(studentId, newStatus) {
            const action = newStatus === 'active' ? '{{ __('student_profiles.Activate') }}' : '{{ __('student_profiles.Deactivate') }}';
            const message = newStatus === 'active'
                ? '{{ __('student_profiles.Are you sure you want to activate this student?') }}'
                : '{{ __('student_profiles.Are you sure you want to deactivate this student?') }}';
            
            // Get current URL parameters to preserve pagination and filters
            const urlParams = new URLSearchParams(window.location.search);
            const queryString = urlParams.toString();
            const url = `/student-profiles/${studentId}/toggle-status${queryString ? '?' + queryString : ''}`;
            
            confirmAction(
                url,
                action,
                message,
                action
            );
        }
        
        function confirmAction(url, title, message, confirmText) {
            if (typeof Alpine !== 'undefined') {
                window.dispatchEvent(new CustomEvent('confirm-show', {
                    detail: {
                        title: title,
                        message: message,
                        confirmText: confirmText,
                        cancelText: '{{ __('student_profiles.Cancel') }}',
                        onConfirm: () => {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = url;
                            form.innerHTML = '@csrf';
                            document.body.appendChild(form);
                            form.submit();
                        }
                    }
                }));
            } else {
                if (confirm(message)) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.innerHTML = '@csrf';
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }
    </script>
</x-app-layout>
