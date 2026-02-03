<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('teacher-profiles.index') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-lg">
                <i class="fas fa-user-plus"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('teacher_profiles.Profiles') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('teacher_profiles.Create Teacher') }}</h2>
            </div>
        </div>
    </x-slot>

    <style>[x-cloak]{display:none;}</style>

    <style>[x-cloak]{display:none;}</style>
    <script src="{{ asset('js/form-validation.js') }}"></script>

    <div class="py-6 sm:py-10" x-data="{
        step: 1,
        total: 7,
        form: {
            name: @js(old('name', '')),
            email: @js(old('email', '')),
            phone: @js(old('phone', '')),
            nrc: @js(old('nrc', '')),
            status: @js(old('status', 'active')),
            is_active: @js(old('is_active', true)),
            department_id: @js(old('department_id')),
            hire_date: @js(old('hire_date', '')),
            basic_salary: @js(old('basic_salary', '')),
            employee_id: @js(old('employee_id', '')),
            position: @js(old('position', '')),
            phone_no: @js(old('phone_no', '')),
            address: @js(old('address', '')),
        },
        errors: {},
        
        nextStep() {
            const validation = validateFormStep(this.form, this.step, 'teacher');
            this.errors = validation.errors;
            
            if (validation.isValid) {
                this.step = Math.min(this.total, this.step + 1);
            } else {
                updateFieldErrorClasses(this.errors);
                showFormNotification('Please fill in all required fields before proceeding', 'error');
            }
        }
    }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-4 sm:p-5">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                        <i class="fas fa-user-plus text-emerald-500"></i>
                        <span>{{ __('teacher_profiles.Add New Teacher') }}</span>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('teacher_profiles.Step') }} <span x-text="step"></span> / <span x-text="total"></span></span>
                </div>
                <div class="mt-3 grid grid-cols-7 gap-1">
                    <template x-for="index in total" :key="index">
                        <div class="h-2 rounded-full" :class="index <= step ? 'bg-emerald-600' : 'bg-gray-200 dark:bg-gray-700'"></div>
                    </template>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <form method="POST" action="{{ route('teacher-profiles.store') }}" class="p-6 sm:p-8 space-y-6" enctype="multipart/form-data">
                    @csrf

                    <!-- Step 1: Basic Information -->
                    <div x-show="step === 1" x-cloak class="space-y-4">
                        <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                            <i class="fas fa-id-badge text-emerald-500"></i>
                            <span>{{ __('teacher_profiles.Basic Information') }}</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Photo') }}</label>
                                <input type="file" name="photo" accept="image/*" class="w-full text-sm text-gray-700 dark:text-gray-200">
                                @error('photo')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Name') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="form.name" required autocomplete="name" 
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500"
                                       :class="errors.name ? 'field-error' : ''">
                                <p x-show="errors.name" x-text="errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Position') }}</label>
                                <input type="text" name="position" x-model="form.position" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Department') }}</label>
                                <select name="department_id" x-model="form.department_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="">{{ __('teacher_profiles.Select department') }}</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Employee ID') }}</label>
                                <input type="text" name="employee_id" x-model="form.employee_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500" placeholder="EMP-XXXX (auto if blank)">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Date of Joining') }}</label>
                                <input type="date" name="hire_date" x-model="form.hire_date" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Monthly Salary') }}</label>
                                <input type="number" step="0.01" name="basic_salary" x-model="form.basic_salary" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Ph. no.') }}</label>
                                <input type="text" name="phone_no" x-model="form.phone_no" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                                @error('phone_no')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Address') }}</label>
                                <input type="text" name="address" x-model="form.address" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Email address') }}</label>
                            <input type="email" x-model="form.email" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500" placeholder="{{ __('teacher_profiles.Use portal email if same') }}">
                        </div>
                    </div>

                    <!-- Step 2: Personal Details -->
                    <div x-show="step === 2" x-cloak class="space-y-4">
                        <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                            <i class="fas fa-address-card text-emerald-500"></i>
                            <span>{{ __('teacher_profiles.Personal Details') }}</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Gender') }}</label>
                                <select name="gender" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="">{{ __('teacher_profiles.Select') }}</option>
                                    <option value="male" @selected(old('gender') === 'male')>{{ __('teacher_profiles.Male') }}</option>
                                    <option value="female" @selected(old('gender') === 'female')>{{ __('teacher_profiles.Female') }}</option>
                                    <option value="other" @selected(old('gender') === 'other')>{{ __('teacher_profiles.Other') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Ethnicity') }}</label>
                                <input type="text" name="ethnicity" value="{{ old('ethnicity') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Religious') }}</label>
                                <input type="text" name="religious" value="{{ old('religious') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.NRC') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="nrc" x-model="form.nrc" required autocomplete="off" 
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500"
                                       :class="errors.nrc ? 'field-error' : ''">
                                <p x-show="errors.nrc" x-text="errors.nrc" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                @error('nrc')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.D.O.B') }}</label>
                                <input type="date" name="dob" value="{{ old('dob') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Education') }}</label>
                                <input type="text" name="qualification" value="{{ old('qualification') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Green card') }}</label>
                                <input type="text" name="green_card" value="{{ old('green_card') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Email address') }}</label>
                                <input type="email" x-model="form.email" autocomplete="email" 
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500" 
                                       placeholder="{{ __('teacher_profiles.Portal email if same') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Academic Information -->
                    <div x-show="step === 3" x-cloak class="space-y-4">
                        <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                            <i class="fas fa-chalkboard text-emerald-500"></i>
                            <span>{{ __('teacher_profiles.Academic Information') }}</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Current Grade') }}</label>
                                <input type="text" name="current_grades" value="{{ old('current_grades') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500" placeholder="e.g. Grade 7">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Current Classes') }}</label>
                                <input type="text" name="current_classes" value="{{ old('current_classes') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500" placeholder="e.g. 7A, 7B">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Responsible Class') }}</label>
                                <input type="text" name="responsible_class" value="{{ old('responsible_class') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Subjects taught') }}</label>
                                <input type="text" name="subjects_taught" value="{{ old('subjects_taught') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500" placeholder="e.g. Math, Science">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Previous School') }}</label>
                            <input type="text" name="previous_school" value="{{ old('previous_school') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>

                    <!-- Step 4: Family Information -->
                    <div x-show="step === 4" x-cloak class="space-y-4">
                        <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                            <i class="fas fa-users text-emerald-500"></i>
                            <span>{{ __('teacher_profiles.Family Information') }}</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Father name') }}</label>
                                <input type="text" name="father_name" value="{{ old('father_name') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Father\'s Ph no.') }}</label>
                                <input type="text" name="father_phone" value="{{ old('father_phone') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Mother name') }}</label>
                                <input type="text" name="mother_name" value="{{ old('mother_name') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Mother\'s Ph no.') }}</label>
                                <input type="text" name="mother_phone" value="{{ old('mother_phone') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Emergency contact ph no.') }}</label>
                            <input type="text" name="emergency_contact" value="{{ old('emergency_contact') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>

                    <!-- Step 5: Marital Status & In-School Relative -->
                    <div x-show="step === 5" x-cloak class="space-y-4">
                        <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                            <i class="fas fa-heart text-emerald-500"></i>
                            <span>{{ __('teacher_profiles.Marital Status & In-School Relative') }}</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Marital Status') }}</label>
                                <select name="marital_status" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="">{{ __('teacher_profiles.Select') }}</option>
                                    <option value="single" @selected(old('marital_status') === 'single')>{{ __('teacher_profiles.Single') }}</option>
                                    <option value="married" @selected(old('marital_status') === 'married')>{{ __('teacher_profiles.Married') }}</option>
                                    <option value="divorced" @selected(old('marital_status') === 'divorced')>{{ __('teacher_profiles.Divorced') }}</option>
                                    <option value="widowed" @selected(old('marital_status') === 'widowed')>{{ __('teacher_profiles.Widowed') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Partner Name') }}</label>
                                <input type="text" name="partner_name" value="{{ old('partner_name') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Partner\'s Ph no.') }}</label>
                                <input type="text" name="partner_phone" value="{{ old('partner_phone') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.In-school Relative Name') }}</label>
                                <input type="text" name="in_school_relative_name" value="{{ old('in_school_relative_name') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Relationship') }}</label>
                            <input type="text" name="in_school_relative_relationship" value="{{ old('in_school_relative_relationship') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>

                    <!-- Step 6: Physical & Medical Information -->
                    <div x-show="step === 6" x-cloak class="space-y-4">
                        <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                            <i class="fas fa-notes-medical text-emerald-500"></i>
                            <span>{{ __('teacher_profiles.Physical & Medical Information') }}</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Height (cm)') }}</label>
                                <input type="number" step="0.01" name="height" value="{{ old('height') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Medicine allergy') }}</label>
                                <input type="text" name="medicine_allergy" value="{{ old('medicine_allergy') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Food allergy') }}</label>
                                <input type="text" name="food_allergy" value="{{ old('food_allergy') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Medical Directory') }}</label>
                            <textarea name="medical_directory" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">{{ old('medical_directory') }}</textarea>
                        </div>
                    </div>

                    <!-- Step 7: Portal Registration -->
                    <div x-show="step === 7" x-cloak class="space-y-4">
                        <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                            <i class="fas fa-clipboard-check text-emerald-500"></i>
                            <span>{{ __('teacher_profiles.Portal Registration') }}</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Portal Email') }} <span class="text-red-500">*</span></label>
                                <input type="email" name="email" x-model="form.email" required autocomplete="email" 
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500"
                                       :class="errors.email ? 'field-error' : ''">
                                <p x-show="errors.email" x-text="errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                @error('email')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Portal Password') }} <span class="text-red-500">*</span></label>
                                <input type="password" name="password" required autocomplete="current-password" 
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500" 
                                       placeholder="********">
                                @error('password')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Phone Number') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="phone" x-model="form.phone" required autocomplete="tel" 
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500"
                                       :class="errors.phone ? 'field-error' : ''">
                                <p x-show="errors.phone" x-text="errors.phone" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                @error('phone')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Profile status') }}</label>
                                <select name="status" x-model="form.status" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="active">{{ __('teacher_profiles.Active') }}</option>
                                    <option value="inactive">{{ __('teacher_profiles.Inactive') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="inline-flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="is_active" x-model="form.is_active" :checked="form.is_active" value="1" class="rounded border-gray-300 dark:border-gray-700 text-emerald-600 shadow-sm focus:ring-emerald-500 dark:bg-gray-900 dark:text-gray-300">
                                <span class="ml-2">{{ __('teacher_profiles.Allow portal login') }}</span>
                            </label>
                        </div>
                        <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-4 text-sm text-gray-600 dark:text-gray-300">
                            {{ __('teacher_profiles.Review the details and submit to create the teacher profile with portal access.') }}
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-2">
                        <a href="{{ route('teacher-profiles.index') }}" class="w-full sm:w-auto px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg font-semibold text-center hover:bg-gray-200 dark:hover:bg-gray-600">{{ __('teacher_profiles.Cancel') }}</a>
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <button type="button" @click="step = Math.max(1, step - 1)" :disabled="step === 1" class="flex-1 sm:flex-none px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50">
                                <i class="fas fa-chevron-left mr-2"></i>{{ __('teacher_profiles.Back') }}
                            </button>
                            <button type="button" x-show="step < total" @click="nextStep()" class="flex-1 sm:flex-none px-4 py-2 bg-emerald-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-sm">
                                {{ __('teacher_profiles.Next step') }} <i class="fas fa-chevron-right ml-2"></i>
                            </button>
                            <button type="submit" x-show="step === total" class="flex-1 sm:flex-none px-4 py-2 bg-emerald-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-sm">
                                {{ __('teacher_profiles.Create Teacher') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
