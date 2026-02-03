<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('student-profiles.index') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                <i class="fas fa-user-plus"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('student_profiles.Profiles') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('student_profiles.Create Student') }}</h2>
            </div>
        </div>
    </x-slot>

    <style>[x-cloak]{display:none;}</style>
    <script src="{{ asset('js/form-validation.js') }}"></script>

                <div class="py-6 sm:py-10" x-data="{
                    step: 1,
                    total: 7,
                    bindExisting: false,
                    form: {
                        user_id: @js(old('user_id')),
                        name: @js(old('name')),
                        email: @js(old('email')),
                        phone: @js(old('phone')),
                        nrc: @js(old('nrc')),
                        password: '',
                        is_active: @js((bool) old('is_active', true)),
                        student_identifier: @js(old('student_identifier')),
                        status: @js(old('status', 'active')),
                        grade_id: @js(old('grade_id')),
                        class_id: @js(old('class_id')),
                        starting_grade_at_school: @js(old('starting_grade_at_school')),
                        current_grade: @js(old('current_grade')),
                        current_class: @js(old('current_class')),
                        guardian_teacher: @js(old('guardian_teacher')),
                        assistant_teacher: @js(old('assistant_teacher')),
                        date_of_joining: @js(old('date_of_joining')),
                        gender: @js(old('gender')),
                        ethnicity: @js(old('ethnicity')),
                        religious: @js(old('religious')),
                        dob: @js(old('dob')),
                        previous_school_name: @js(old('previous_school_name')),
                        previous_school_address: @js(old('previous_school_address')),
                        address: @js(old('address')),
                        father_name: @js(old('father_name')),
                        father_nrc: @js(old('father_nrc')),
                        father_phone_no: @js(old('father_phone_no')),
                        father_occupation: @js(old('father_occupation')),
                        mother_name: @js(old('mother_name')),
                        mother_nrc: @js(old('mother_nrc')),
                        mother_phone_no: @js(old('mother_phone_no')),
                        mother_occupation: @js(old('mother_occupation')),
                        emergency_contact_phone_no: @js(old('emergency_contact_phone_no')),
                        in_school_relative_name: @js(old('in_school_relative_name')),
                        in_school_relative_grade: @js(old('in_school_relative_grade')),
                        in_school_relative_relationship: @js(old('in_school_relative_relationship')),
                        blood_type: @js(old('blood_type')),
                        weight: @js(old('weight')),
                        height: @js(old('height')),
                        medicine_allergy: @js(old('medicine_allergy')),
                        food_allergy: @js(old('food_allergy')),
                        medical_directory: @js(old('medical_directory')),
                    },
                    errors: {},
                    
                    nextStep() {
                        const validation = validateFormStep(this.form, this.step, 'student');
                        this.errors = validation.errors;
                        
                        if (validation.isValid) {
                            this.step = Math.min(this.total, this.step + 1);
                        } else {
                            updateFieldErrorClasses(this.errors);
                            showFormNotification('Please fill in all required fields before proceeding', 'error');
                        }
                    }
                }">
                    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-4 sm:p-5">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                    <i class="fas fa-user-graduate text-indigo-500"></i>
                                    <span>{{ __('student_profiles.New Student Wizard') }}</span>
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('student_profiles.Step') }} <span x-text="step"></span> / <span x-text="total"></span></span>
                            </div>
                            <div class="mt-3 grid grid-cols-7 gap-1">
                                <template x-for="index in total" :key="index">
                                    <div class="h-2 rounded-full" :class="index <= step ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700'"></div>
                                </template>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                            <form method="POST" action="{{ route('student-profiles.store') }}" class="p-6 sm:p-8 space-y-6">
                                @csrf

                                <!-- Step 1: Portal Account & ID -->
                                <div x-show="step === 1" x-cloak class="space-y-4">
                                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                        <i class="fas fa-id-card-alt text-indigo-500"></i>
                                        <span>{{ __('student_profiles.Portal Account & Student ID') }}</span>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <label class="inline-flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                                            <input type="checkbox" x-model="bindExisting" class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-300">
                                            <span class="ml-2">{{ __('student_profiles.Bind existing student portal user') }}</span>
                                        </label>
                                    </div>

                                    <div x-show="bindExisting" x-cloak class="space-y-3">
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Existing Portal User') }}</label>
                                        <select name="user_id" x-model="form.user_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">{{ __('student_profiles.Select a student account') }}</option>
                                            @foreach($studentUsers as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }} — {{ $user->email }}</option>
                                            @endforeach
                                        </select>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('student_profiles.If binding an existing user, portal credentials stay the same unless you provide a new password below.') }}</p>
                                        @error('user_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-show="!bindExisting" x-cloak>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Student Name') }} <span class="text-red-500">*</span></label>
                                            <input type="text" name="name" x-model="form.name" :disabled="bindExisting" autocomplete="name" 
                                                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                                   :class="errors.name ? 'field-error' : ''">
                                            <p x-show="errors.name" x-text="errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                            @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Portal Email') }}</label>
                                            <input type="email" name="email" x-model="form.email" :disabled="bindExisting" autocomplete="email" 
                                                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                            @error('email')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-show="!bindExisting" x-cloak>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Phone') }}</label>
                                            <input type="text" name="phone" x-model="form.phone" :disabled="bindExisting" autocomplete="tel" 
                                                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                            @error('phone')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.NRC / ID') }} <span class="text-red-500">*</span></label>
                                            <input type="text" name="nrc" x-model="form.nrc" :disabled="bindExisting" autocomplete="off" 
                                                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                                   :class="errors.nrc ? 'field-error' : ''">
                                            <p x-show="errors.nrc" x-text="errors.nrc" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                            @error('nrc')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div x-show="!bindExisting" x-cloak>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Portal Password') }}</label>
                                            <input type="password" name="password" :disabled="bindExisting" autocomplete="current-password" 
                                                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500" 
                                                   placeholder="********">
                                            @error('password')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                        </div>
                                        <div class="flex items-center gap-3 mt-7">
                                            <label class="inline-flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                <input type="checkbox" name="is_active" x-model="form.is_active" :checked="form.is_active" value="1" class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-300">
                                                <span class="ml-2">{{ __('student_profiles.Allow portal login') }}</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Student Identifier') }}</label>
                                            <input type="text" name="student_identifier" x-model="form.student_identifier" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500" placeholder="STD-XXXX (auto if blank)">
                                            @error('student_identifier')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Profile Status') }}</label>
                                            <select name="status" x-model="form.status" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="active">{{ __('student_profiles.Active') }}</option>
                                                <option value="inactive">{{ __('student_profiles.Inactive') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 2: School Placement -->
                                <div x-show="step === 2" x-cloak class="space-y-4">
                                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                        <i class="fas fa-school text-indigo-500"></i>
                                        <span>{{ __('student_profiles.School Placement') }}</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Grade') }}</label>
                                            <select name="grade_id" x-model="form.grade_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="">{{ __('student_profiles.Select grade') }}</option>
                                                @foreach($grades as $grade)
                                                    <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('grade_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Class') }}</label>
                                            <select name="class_id" x-model="form.class_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="">{{ __('student_profiles.Select class') }}</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('class_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Starting Grade at School') }}</label>
                                            <input type="text" name="starting_grade_at_school" x-model="form.starting_grade_at_school" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Current Grade') }}</label>
                                            <input type="text" name="current_grade" x-model="form.current_grade" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Current Class') }}</label>
                                            <input type="text" name="current_class" x-model="form.current_class" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Date of Joining') }}</label>
                                            <input type="date" name="date_of_joining" x-model="form.date_of_joining" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Guardian Teacher') }}</label>
                                            <input type="text" name="guardian_teacher" x-model="form.guardian_teacher" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Assistant Teacher') }}</label>
                                            <input type="text" name="assistant_teacher" x-model="form.assistant_teacher" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 3: Personal Details -->
                                <div x-show="step === 3" x-cloak class="space-y-4">
                                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                        <i class="fas fa-user text-indigo-500"></i>
                                        <span>{{ __('student_profiles.Personal Details') }}</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Gender') }}</label>
                                            <select name="gender" x-model="form.gender" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="">{{ __('student_profiles.Select') }}</option>
                                                <option value="male" @selected(old('gender') === 'male')>{{ __('student_profiles.Male') }}</option>
                                                <option value="female" @selected(old('gender') === 'female')>{{ __('student_profiles.Female') }}</option>
                                                <option value="other" @selected(old('gender') === 'other')>{{ __('student_profiles.Other') }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Ethnicity') }}</label>
                                            <input type="text" name="ethnicity" x-model="form.ethnicity" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Religious') }}</label>
                                            <input type="text" name="religious" x-model="form.religious" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.DOB') }}</label>
                                            <input type="date" name="dob" x-model="form.dob" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Address') }}</label>
                                            <input type="text" name="address" x-model="form.address" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div></div>
                                    </div>
                                </div>

                                <!-- Step 4: Previous School & Address -->
                                <div x-show="step === 4" x-cloak class="space-y-4">
                                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                        <i class="fas fa-university text-indigo-500"></i>
                                        <span>{{ __('student_profiles.Previous School & Address') }}</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Previous School Name') }}</label>
                                            <input type="text" name="previous_school_name" x-model="form.previous_school_name" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Previous School Address') }}</label>
                                            <input type="text" name="previous_school_address" x-model="form.previous_school_address" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 5: Family & Emergency -->
                                <div x-show="step === 5" x-cloak class="space-y-4">
                                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                        <i class="fas fa-users text-indigo-500"></i>
                                        <span>{{ __('student_profiles.Family & Emergency') }}</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Father Name') }}</label>
                                            <input type="text" name="father_name" x-model="form.father_name" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Father NRC') }}</label>
                                            <input type="text" name="father_nrc" x-model="form.father_nrc" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Father Phone No.') }}</label>
                                            <input type="text" name="father_phone_no" x-model="form.father_phone_no" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Father Occupation') }}</label>
                                            <input type="text" name="father_occupation" x-model="form.father_occupation" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Mother Name') }}</label>
                                            <input type="text" name="mother_name" x-model="form.mother_name" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Mother NRC') }}</label>
                                            <input type="text" name="mother_nrc" x-model="form.mother_nrc" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Mother Phone No.') }}</label>
                                            <input type="text" name="mother_phone_no" x-model="form.mother_phone_no" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Mother Occupation') }}</label>
                                            <input type="text" name="mother_occupation" x-model="form.mother_occupation" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Emergency Contact Phone') }}</label>
                                            <input type="text" name="emergency_contact_phone_no" x-model="form.emergency_contact_phone_no" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.In-school Relative Name') }}</label>
                                            <input type="text" name="in_school_relative_name" x-model="form.in_school_relative_name" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.In-school Relative Grade') }}</label>
                                            <input type="text" name="in_school_relative_grade" x-model="form.in_school_relative_grade" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Relationship') }}</label>
                                            <input type="text" name="in_school_relative_relationship" x-model="form.in_school_relative_relationship" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 6: Medical & Health -->
                                <div x-show="step === 6" x-cloak class="space-y-4">
                                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                        <i class="fas fa-notes-medical text-indigo-500"></i>
                                        <span>{{ __('student_profiles.Medical & Health') }}</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Blood Type') }}</label>
                                            <input type="text" name="blood_type" x-model="form.blood_type" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500" placeholder="A+/O-/...">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Weight (kg)') }}</label>
                                            <input type="number" step="0.1" name="weight" x-model="form.weight" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Height (cm)') }}</label>
                                            <input type="number" step="0.1" name="height" x-model="form.height" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Medicine Allergy') }}</label>
                                            <input type="text" name="medicine_allergy" x-model="form.medicine_allergy" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Food Allergy') }}</label>
                                            <input type="text" name="food_allergy" x-model="form.food_allergy" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Medical Directory') }}</label>
                                        <textarea name="medical_directory" rows="3" x-model="form.medical_directory" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                    </div>
                                </div>

                                <!-- Step 7: Review & Submit -->
                                <div x-show="step === 7" x-cloak class="space-y-4">
                                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                        <i class="fas fa-clipboard-check text-indigo-500"></i>
                                        <span>{{ __('student_profiles.Review & Submit') }}</span>
                                    </div>
                                    <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-4 text-sm text-gray-700 dark:text-gray-300 space-y-2">
                                        <p>{{ __('student_profiles.Review the student portal account, placement, and family details before submitting.') }}</p>
                                        <p>{{ __('student_profiles.You can revisit any step to adjust data. Passwords are optional when binding an existing user; providing one will reset the portal credentials.') }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-2">
                                    <a href="{{ route('student-profiles.index') }}" class="w-full sm:w-auto px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg font-semibold text-center hover:bg-gray-200 dark:hover:bg-gray-600">{{ __('student_profiles.Cancel') }}</a>
                                    <div class="flex items-center gap-3 w-full sm:w-auto">
                                        <button type="button" @click="step = Math.max(1, step - 1)" :disabled="step === 1" class="flex-1 sm:flex-none px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50">
                                            <i class="fas fa-chevron-left mr-2"></i>{{ __('student_profiles.Back') }}
                                        </button>
                                        <button type="button" x-show="step < total" @click="nextStep()" class="flex-1 sm:flex-none px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-sm">
                                            {{ __('student_profiles.Next step') }} <i class="fas fa-chevron-right ml-2"></i>
                                        </button>
                                        <button type="submit" x-show="step === total" class="flex-1 sm:flex-none px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-sm">
                                            {{ __('student_profiles.Create Student') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </x-app-layout>
