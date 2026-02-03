<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                <i class="fas fa-user-graduate"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('student_profiles.Profiles') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('student_profiles.Student Profile') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden">
        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">
            <x-back-link 
                :href="route('student-profiles.index')"
                :text="__('student_profiles.Back to Student Profiles')"
            />
            <!-- Basic Information Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                        <i class="fas fa-info-circle text-blue-500"></i>
                        {{ __('student_profiles.Basic Information') }}
                    </h4>
                    @can('manage student profiles')
                        <a href="{{ route('student-profiles.edit', $studentProfile) }}" class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all">
                            <i class="fas fa-edit mr-1.5"></i>{{ __('student_profiles.Edit') }}
                        </a>
                    @endcan
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Photo') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                    <img 
                                        src="{{ avatar_url($studentProfile->photo_path, 'student') }}" 
                                        alt="{{ $studentProfile->user?->name }}" 
                                        class="w-24 h-24 rounded-lg object-cover border border-gray-200 dark:border-gray-700"
                                    >
                                </td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Name') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100 font-medium">{{ $studentProfile->user?->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Student ID') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->student_identifier ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Date of Joining') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->date_of_joining?->format('Y-m-d') ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Personal Information Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                        <i class="fas fa-user text-blue-500"></i>
                        {{ __('student_profiles.Personal Information') }}
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Gender') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ ucfirst($studentProfile->gender ?? '—') }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Ethnicity') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->ethnicity ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Religious') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->religious ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.NRC') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->nrc ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.D.O.B') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->dob?->format('Y-m-d') ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Academic Information Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                        <i class="fas fa-graduation-cap text-blue-500"></i>
                        {{ __('student_profiles.Academic Information') }}
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Starting grade at school') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->starting_grade_at_school ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Current Grade') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->grade?->name ?? $studentProfile->current_grade ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Current Class') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->classModel?->name ?? $studentProfile->current_class ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Guardian Teacher') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->guardian_teacher ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Assistant Teacher') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->assistant_teacher ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Previous School') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->previous_school_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Address') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->address ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Family & Relationship Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                        <i class="fas fa-users text-blue-500"></i>
                        {{ __('student_profiles.Family & Relationship') }}
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Father name') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->father_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Father NRC') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->father_nrc ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Father Phone No.') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->father_phone_no ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Father Occupation') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->father_occupation ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Mother name') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->mother_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Mother NRC') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->mother_nrc ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Mother Phone No.') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->mother_phone_no ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Mother Occupation') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->mother_occupation ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Emergency contact ph no.') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->emergency_contact_phone_no ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.In-school relative - Name') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->in_school_relative_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.In-school relative - Grade') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->in_school_relative_grade ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.In-school relative - Relationship') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->in_school_relative_relationship ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Medical Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                        <i class="fas fa-heartbeat text-blue-500"></i>
                        {{ __('student_profiles.Medical') }}
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Weight') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->weight ? $studentProfile->weight . ' kg' : '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Height') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->height ? $studentProfile->height . ' cm' : '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Blood Type') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->blood_type ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Medicine allergy') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->medicine_allergy ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Food allergy') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $studentProfile->food_allergy ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('student_profiles.Medical Directory') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $studentProfile->medical_directory ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
