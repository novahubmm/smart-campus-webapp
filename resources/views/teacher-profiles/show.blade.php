<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-lg">
                <i class="fas fa-chalkboard-teacher"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('teacher_profiles.Profiles') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('teacher_profiles.Teacher Profile') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden">
        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">
            <x-back-link 
                :href="route('teacher-profiles.index')"
                :text="__('teacher_profiles.Back to Teacher Profiles')"
            />

            <!-- Basic Information Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                        <i class="fas fa-info-circle text-emerald-500"></i>
                        {{ __('teacher_profiles.Basic Information') }}
                    </h4>
                    @can(App\Enums\PermissionEnum::MANAGE_TEACHER_PROFILES->value)
                        <a href="{{ route('teacher-profiles.edit', $profile) }}" class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all">
                            <i class="fas fa-edit mr-1.5"></i>{{ __('teacher_profiles.Edit') }}
                        </a>
                    @endcan
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Photo') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                    <img src="{{ avatar_url($profile->photo_path, 'teacher') }}" alt="{{ $profile->user->name }}" class="w-24 h-24 rounded-lg object-cover border border-gray-200 dark:border-gray-700">
                                </td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Name') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100 font-medium">{{ $profile->user->name }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Employee ID') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->employee_id ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Ph. no.') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->phone_no ?? $profile->user->phone ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Address') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->address ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Email address') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->user->email }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Status') }}</th>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $profile->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' }}">
                                        {{ ucfirst($profile->status ?? 'active') }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Personal Information Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                        <i class="fas fa-user text-emerald-500"></i>
                        {{ __('teacher_profiles.Personal Information') }}
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Gender') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ ucfirst($profile->gender ?? '—') }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Ethnicity') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->ethnicity ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Religious') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->religious ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.NRC') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->nrc ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.D.O.B') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->dob?->format('Y-m-d') ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Organizational Information Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                        <i class="fas fa-building text-emerald-500"></i>
                        {{ __('teacher_profiles.Organizational Information') }}
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Position') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->position ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Department') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->department?->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Date of Joining') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->hire_date?->format('Y-m-d') ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Basic Salary') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->basic_salary ? number_format($profile->basic_salary, 0).' MMK' : '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Education Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                        <i class="fas fa-graduation-cap text-emerald-500"></i>
                        {{ __('teacher_profiles.Education') }}
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Qualification') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->qualification ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Previous experience (Year)') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->previous_experience_years ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Green card') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->green_card ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Current Grade') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                    @php
                                        // Get unique grades from subjects taught
                                        $grades = $profile->subjects->flatMap(function($subject) {
                                            return $subject->grades->pluck('name');
                                        })->unique()->sort()->values();
                                    @endphp
                                    {{ $grades->isNotEmpty() ? $grades->implode(', ') : '—' }}
                                </td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Current Classes') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                    @php
                                        // Get unique classes from periods/timetables where this teacher teaches
                                        $periods = \App\Models\Period::with(['timetable.schoolClass.grade'])
                                            ->where('teacher_profile_id', $profile->id)
                                            ->get();
                                        
                                        $classNames = $periods->map(function($period) {
                                            if ($period->timetable && $period->timetable->schoolClass && $period->timetable->schoolClass->grade) {
                                                return $period->timetable->schoolClass->grade->name . ' ' . $period->timetable->schoolClass->name;
                                            }
                                            return null;
                                        })->filter()->unique()->sort()->values();
                                    @endphp
                                    {{ $classNames->isNotEmpty() ? $classNames->implode(', ') : '—' }}
                                </td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Responsible Class') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                    @php
                                        // Get classes where this teacher is the class teacher (with grade name)
                                        $responsibleClasses = $profile->classes->map(function($class) {
                                            return $class->grade->name . ' ' . $class->name;
                                        })->sort()->values();
                                    @endphp
                                    {{ $responsibleClasses->isNotEmpty() ? $responsibleClasses->implode(', ') : '—' }}
                                </td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Subjects taught') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                    @php
                                        // Get subjects from subject_teacher pivot table
                                        $subjectNames = $profile->subjects->pluck('name')->sort()->values();
                                    @endphp
                                    {{ $subjectNames->isNotEmpty() ? $subjectNames->implode(', ') : '—' }}
                                </td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Previous School') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->previous_school ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Family & Relationship Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                        <i class="fas fa-users text-emerald-500"></i>
                        {{ __('teacher_profiles.Family & Relationship') }}
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Father name') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->father_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __("Father's Ph no.") }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->father_phone ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Mother name') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->mother_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __("Mother's Ph no.") }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->mother_phone ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Emergency contact ph no.') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->emergency_contact ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Marital Status') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ ucfirst($profile->marital_status ?? '—') }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Partner Name') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->partner_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __("Partner's Ph no.") }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->partner_phone ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.In-school relative - Name') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->in_school_relative_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.In-school relative - Relationship') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->in_school_relative_relationship ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Medical Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                        <i class="fas fa-heartbeat text-emerald-500"></i>
                        {{ __('teacher_profiles.Medical') }}
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Height') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->height ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Weight') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->weight ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Blood Type') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->blood_type ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Medicine allergy') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->medicine_allergy ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Food allergy') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $profile->food_allergy ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('teacher_profiles.Medical Directory') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $profile->medical_directory ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
