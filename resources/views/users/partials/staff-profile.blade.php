@php
    $profile = $user->staffProfile;
    $title = isset($showAsAdmin) && $showAsAdmin ? __('Admin Details') : __('Staff Profile');
@endphp

<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-6 space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
        @if($profile?->photo_path)
            <img src="{{ avatar_url($profile->photo_path, 'staff') }}" class="h-16 w-16 rounded-lg object-cover" alt="{{ $user->name }}">
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm text-gray-800 dark:text-gray-200">
        <div><span class="font-semibold">{{ __('staff_profile.Position') }}:</span> {{ $profile?->position ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('staff_profile.Department') }}:</span> {{ $profile?->department?->name ?? $profile?->department_id ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('staff_profile.Employee ID') }}:</span> {{ $profile?->employee_id ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('staff_profile.Hire Date') }}:</span> {{ optional($profile?->hire_date)->format('Y-m-d') ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('staff_profile.Monthly Salary') }}:</span> {{ $profile?->basic_salary ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('staff_profile.Phone') }}:</span> {{ $profile?->phone_no ?? $user->phone ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('staff_profile.Address') }}:</span> {{ $profile?->address ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('staff_profile.NRC') }}:</span> {{ $user->nrc ?? $profile?->nrc ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('staff_profile.Status') }}:</span> {{ $profile?->status ?? '—' }}</div>
    </div>

    <div class="pt-2 border-t border-gray-200 dark:border-gray-700 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm text-gray-800 dark:text-gray-200">
        <div><span class="font-semibold">{{ __('staff_profile.Gender') }}:</span> {{ $profile?->gender ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('staff_profile.Ethnicity') }}:</span> {{ $profile?->ethnicity ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('staff_profile.Religious') }}:</span> {{ $profile?->religious ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('staff_profile.DOB') }}:</span> {{ optional($profile?->dob)->format('Y-m-d') ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('staff_profile.Education') }}:</span> {{ $profile?->qualification ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('staff_profile.Experience (years)') }}:</span> {{ $profile?->previous_experience_years ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('staff_profile.Green card') }}:</span> {{ $profile?->green_card ?? '—' }}</div>
    </div>

    <div class="pt-2 border-t border-gray-200 dark:border-gray-700 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm text-gray-800 dark:text-gray-200">
        <div><span class="font-semibold">{{ __('staff_profile.Father') }}:</span> {{ $profile?->father_name ?? '—' }} ({{ $profile?->father_phone ?? '—' }})</div>
        <div><span class="font-semibold">{{ __('staff_profile.Mother') }}:</span> {{ $profile?->mother_name ?? '—' }} ({{ $profile?->mother_phone ?? '—' }})</div>
        <div><span class="font-semibold">{{ __('staff_profile.Emergency') }}:</span> {{ $profile?->emergency_contact ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('staff_profile.Marital') }}:</span> {{ $profile?->marital_status ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('staff_profile.Partner') }}:</span> {{ $profile?->partner_name ?? '—' }} ({{ $profile?->partner_phone ?? '—' }})</div>
        <div><span class="font-semibold">{{ __('staff_profile.In-school relative') }}:</span> {{ $profile?->relative_name ?? '—' }} ({{ $profile?->relative_relationship ?? '—' }})</div>
    </div>

    <div class="pt-2 border-t border-gray-200 dark:border-gray-700 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm text-gray-800 dark:text-gray-200">
        <div><span class="font-semibold">{{ __('staff_profile.Height') }}:</span> {{ $profile?->height ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('staff_profile.Weight') }}:</span> {{ $profile?->weight ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('staff_profile.Blood type') }}:</span> {{ $profile?->blood_type ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('staff_profile.Medicine allergy') }}:</span> {{ $profile?->medicine_allergy ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('staff_profile.Food allergy') }}:</span> {{ $profile?->food_allergy ?? '—' }}</div>
        <div class="sm:col-span-2 lg:col-span-3">
            <span class="font-semibold">{{ __('staff_profile.Medical directory') }}:</span>
            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $profile?->medical_directory ?? '—' }}</p>
        </div>
    </div>
</div>
