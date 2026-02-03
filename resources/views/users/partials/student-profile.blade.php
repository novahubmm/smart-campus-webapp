@php $profile = $user->studentProfile; @endphp

<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-6 space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('student_profile.Student Profile') }}</h3>
        @if($profile?->photo_path)
            <img src="{{ avatar_url($profile->photo_path, 'student') }}" class="h-16 w-16 rounded-lg object-cover" alt="{{ $user->name }}">
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm text-gray-800 dark:text-gray-200">
        <div><span class="font-semibold">{{ __('student_profile.Student ID') }}:</span> {{ $profile?->student_identifier ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('student_profile.Starting Grade') }}:</span> {{ $profile?->starting_grade_at_school ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('student_profile.Current Grade') }}:</span> {{ $profile?->current_grade ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('student_profile.Current Class') }}:</span> {{ $profile?->current_class ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('student_profile.Guardian Teacher') }}:</span> {{ $profile?->guardian_teacher ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('student_profile.Assistant Teacher') }}:</span> {{ $profile?->assistant_teacher ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('student_profile.Joining Date') }}:</span> {{ optional($profile?->date_of_joining)->format('Y-m-d') ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('student_profile.Status') }}:</span> {{ $profile?->status ?? '—' }}</div>
    </div>

    <div class="pt-2 border-t border-gray-200 dark:border-gray-700 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm text-gray-800 dark:text-gray-200">
        <div><span class="font-semibold">{{ __('student_profile.Gender') }}:</span> {{ $profile?->gender ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('student_profile.Ethnicity') }}:</span> {{ $profile?->ethnicity ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('student_profile.Religious') }}:</span> {{ $profile?->religious ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('student_profile.DOB') }}:</span> {{ optional($profile?->dob)->format('Y-m-d') ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('student_profile.Address') }}:</span> {{ $profile?->address ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('student_profile.Previous School') }}:</span> {{ $profile?->previous_school_name ?? '—' }}</div>
    </div>

    <div class="pt-2 border-t border-gray-200 dark:border-gray-700 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm text-gray-800 dark:text-gray-200">
        <div><span class="font-semibold">{{ __('student_profile.Father') }}:</span> {{ $profile?->father_name ?? '—' }} ({{ $profile?->father_phone_no ?? '—' }})</div>
        <div><span class="font-semibold">{{ __('student_profile.Mother') }}:</span> {{ $profile?->mother_name ?? '—' }} ({{ $profile?->mother_phone_no ?? '—' }})</div>
        <div><span class="font-semibold">{{ __('student_profile.Emergency Contact') }}:</span> {{ $profile?->emergency_contact_phone_no ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('student_profile.In-school Relative') }}:</span> {{ $profile?->in_school_relative_name ?? '—' }} ({{ $profile?->in_school_relative_relationship ?? '—' }})</div>
    </div>

    <div class="pt-2 border-t border-gray-200 dark:border-gray-700 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm text-gray-800 dark:text-gray-200">
        <div><span class="font-semibold">{{ __('student_profile.Blood type') }}:</span> {{ $profile?->blood_type ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('student_profile.Weight') }}:</span> {{ $profile?->weight ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('student_profile.Height') }}:</span> {{ $profile?->height ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('student_profile.Medicine allergy') }}:</span> {{ $profile?->medicine_allergy ?? '—' }}</div>
        <div><span class="font-semibold">{{ __('student_profile.Food allergy') }}:</span> {{ $profile?->food_allergy ?? '—' }}</div>
        <div class="sm:col-span-2 lg:col-span-3">
            <span class="font-semibold">{{ __('student_profile.Medical directory') }}:</span>
            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $profile?->medical_directory ?? '—' }}</p>
        </div>
    </div>
</div>
