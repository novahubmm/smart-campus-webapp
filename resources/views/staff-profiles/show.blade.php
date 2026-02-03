<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg">
                <i class="fas fa-users-cog"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('staff_profiles.Profiles') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('staff_profiles.Staff Profile') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden">
        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">
            <x-back-link 
                :href="route('staff-profiles.index')"
                :text="__('staff_profiles.Back to Staff Profiles')"
            />
            <!-- Basic Information Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                        <i class="fas fa-info-circle text-amber-500"></i>
                        {{ __('staff_profiles.Basic Information') }}
                    </h4>
                    @can(App\Enums\PermissionEnum::MANAGE_STAFF_PROFILES->value)
                        <a href="{{ route('staff-profiles.edit', $staffProfile) }}" class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all">
                            <i class="fas fa-edit mr-1.5"></i>{{ __('staff_profiles.Edit') }}
                        </a>
                    @endcan
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Photo') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                    <img 
                                        src="{{ avatar_url($staffProfile->photo_path, 'staff') }}" 
                                        alt="{{ $staffProfile->user->name }}" 
                                        class="w-24 h-24 rounded-lg object-cover border border-gray-200 dark:border-gray-700"
                                    >
                                </td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Name') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100 font-medium">{{ $staffProfile->user->name }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Employee ID') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->employee_id ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Ph. no.') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->phone_no ?? $staffProfile->user->phone ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Address') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->address ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Email address') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->user->email }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Status') }}</th>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $staffProfile->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' }}">
                                        {{ ucfirst($staffProfile->status ?? 'active') }}
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
                        <i class="fas fa-user text-amber-500"></i>
                        {{ __('staff_profiles.Personal Information') }}
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Gender') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ ucfirst($staffProfile->gender ?? '—') }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Ethnicity') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->ethnicity ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Religious') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->religious ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.NRC') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->nrc ?? $staffProfile->user->nrc ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.D.O.B') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->dob?->format('Y-m-d') ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Organizational Information Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                        <i class="fas fa-building text-amber-500"></i>
                        {{ __('staff_profiles.Organizational Information') }}
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Position') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->position ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Department') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->department?->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Date of Joining') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->hire_date?->format('Y-m-d') ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Basic Salary') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->basic_salary ? number_format($staffProfile->basic_salary, 0).' MMK' : '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Education Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                        <i class="fas fa-graduation-cap text-amber-500"></i>
                        {{ __('staff_profiles.Education') }}
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Qualification') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->qualification ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Previous experience (Year)') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->previous_experience_years ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Green card') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->green_card ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Family & Relationship Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                        <i class="fas fa-users text-amber-500"></i>
                        {{ __('staff_profiles.Family & Relationship') }}
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Father name') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->father_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __("staff_profiles.Father's Ph no.") }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->father_phone ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Mother name') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->mother_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __("staff_profiles.Mother's Ph no.") }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->mother_phone ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Emergency contact ph no.') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->emergency_contact ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Marital Status') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ ucfirst($staffProfile->marital_status ?? '—') }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Partner Name') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->partner_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __("staff_profiles.Partner's Ph no.") }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->partner_phone ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.In-school relative - Name') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->relative_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.In-school relative - Relationship') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->relative_relationship ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Medical Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                        <i class="fas fa-heartbeat text-amber-500"></i>
                        {{ __('staff_profiles.Medical') }}
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Height') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->height ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Weight') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->weight ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Blood Type') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->blood_type ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Medicine allergy') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->medicine_allergy ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Food allergy') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $staffProfile->food_allergy ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('staff_profiles.Medical Directory') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $staffProfile->medical_directory ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
