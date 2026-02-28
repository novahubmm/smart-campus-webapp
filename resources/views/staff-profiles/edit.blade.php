<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('staff-profiles.index') }}"
                class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <span
                class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg">
                <i class="fas fa-user-edit"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('staff_profiles.Profiles') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('staff_profiles.Edit Staff') }}</h2>
            </div>
        </div>
    </x-slot>

    <style>
        [x-cloak] {
            display: none;
        }
    </style>
    <script src="{{ asset('js/form-validation.js') }}"></script>

    <div class="py-6 sm:py-10 overflow-x-hidden" x-data="{
        step: 1,
        total: 6,
        form: {
            name: @js(old('name', $profile->user->name)),
            email: @js(old('email', $profile->user->email)),
            phone: @js(old('phone', $profile->user->phone)),
            nrc: @js(old('nrc', $profile->user->nrc)),
            is_active: @js((bool) old('is_active', $profile->user->is_active)),
            status: @js(old('status', $profile->status ?? 'active')),
            employee_id: @js(old('employee_id', $profile->employee_id)),
            position: @js(old('position', $profile->position)),
            department_id: @js(old('department_id', $profile->department_id)),
            hire_date: @js(old('hire_date', optional($profile->hire_date)->format('Y-m-d'))),
            basic_salary: @js(old('basic_salary', $profile->basic_salary)),
            phone_no: @js(old('phone_no', $profile->phone_no)),
            address: @js(old('address', $profile->address)),
            gender: @js(old('gender', $profile->gender)),
            ethnicity: @js(old('ethnicity', $profile->ethnicity)),
            religious: @js(old('religious', $profile->religious)),
            dob: @js(old('dob', optional($profile->dob)->format('Y-m-d'))),
            qualification: @js(old('qualification', $profile->qualification)),
            green_card: @js(old('green_card', $profile->green_card)),
            father_name: @js(old('father_name', $profile->father_name)),
            father_phone: @js(old('father_phone', $profile->father_phone)),
            mother_name: @js(old('mother_name', $profile->mother_name)),
            mother_phone: @js(old('mother_phone', $profile->mother_phone)),
            emergency_contact: @js(old('emergency_contact', $profile->emergency_contact)),
            marital_status: @js(old('marital_status', $profile->marital_status)),
            partner_name: @js(old('partner_name', $profile->partner_name)),
            partner_phone: @js(old('partner_phone', $profile->partner_phone)),
            relative_name: @js(old('relative_name', $profile->relative_name)),
            relative_relationship: @js(old('relative_relationship', $profile->relative_relationship)),
            height: @js(old('height', $profile->height)),
            medicine_allergy: @js(old('medicine_allergy', $profile->medicine_allergy)),
            food_allergy: @js(old('food_allergy', $profile->food_allergy)),
            medical_directory: @js(old('medical_directory', $profile->medical_directory)),
        },
        errors: {},
        
        nextStep() {
            const validation = validateFormStep(this.form, this.step, 'staff');
            this.errors = validation.errors;
            
            if (validation.isValid) {
                this.step = Math.min(this.total, this.step + 1);
            } else {
                updateFieldErrorClasses(this.errors);
                showFormNotification('Please fill in all required fields before proceeding', 'error');
            }
        }
    }">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-4">
            <div
                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-4 sm:p-5">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                        <i class="fas fa-user-cog text-amber-500"></i>
                        <span>{{ __('staff_profiles.Update Staff') }}</span>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('staff_profiles.Step') }} <span
                            x-text="step"></span> / <span x-text="total"></span></span>
                </div>
                <div class="mt-3 grid grid-cols-6 gap-1">
                    <template x-for="index in total" :key="index">
                        <div class="h-2 rounded-full"
                            :class="index <= step ? 'bg-amber-600' : 'bg-gray-200 dark:bg-gray-700'"></div>
                    </template>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <form method="POST" action="{{ route('staff-profiles.update', $profile) }}" class="p-6 sm:p-8 space-y-6"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Step 1: Basic Information -->
                    <div x-show="step === 1" x-cloak class="space-y-4">
                        <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                            <i class="fas fa-id-badge text-amber-500"></i>
                            <span>{{ __('staff_profiles.Basic Information') }}</span>
                        </div>

                        <!-- Photo Upload with Preview -->
                        <div class="flex items-start gap-6 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                            <div class="flex-shrink-0">
                                <div class="relative">
                                    <img id="photo-preview" src="{{ avatar_url($profile->photo_path, 'staff') }}"
                                        alt="{{ $profile->user->name }}"
                                        class="w-24 h-24 rounded-xl object-cover border-2 border-gray-200 dark:border-gray-700 shadow-sm">
                                    <div
                                        class="absolute -bottom-2 -right-2 w-8 h-8 bg-amber-500 rounded-full flex items-center justify-center shadow-lg">
                                        <i class="fas fa-user text-white text-sm"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-1">
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Photo') }}</label>
                                <input type="file" name="photo" accept="image/jpeg,image/png,image/jpg,image/gif"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-200 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 dark:file:bg-amber-900/30 dark:file:text-amber-300"
                                    onchange="previewStaffPhoto(this)">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ __('staff_profiles.Leave blank to keep current photo.') }} JPG, PNG, GIF. Max 2MB
                                </p>
                                @error('photo')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Name') }}
                                    <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="form.name" required autocomplete="name"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500"
                                    :class="errors.name ? 'field-error' : ''">
                                <p x-show="errors.name" x-text="errors.name"
                                    class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Position') }}</label>
                                <input type="text" name="position" x-model="form.position"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Department') }}</label>
                                <select name="department_id" x-model="form.department_id"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                                    <option value="">{{ __('staff_profiles.Select department') }}</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Employee ID') }}</label>
                                <input type="text" name="employee_id" x-model="form.employee_id"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500"
                                    placeholder="STF-XXXX (auto if blank)">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Date of Joining') }}</label>
                                <input type="date" name="hire_date" x-model="form.hire_date"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Monthly Salary') }}</label>
                                <input type="number" step="0.01" name="basic_salary" x-model="form.basic_salary"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                            </div>
                        </div>
                    </div>


                    <!-- Step 2: Personal Details -->
                    <div x-show="step === 2" x-cloak class="space-y-4">
                        <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                            <i class="fas fa-address-card text-amber-500"></i>
                            <span>{{ __('staff_profiles.Personal Details') }}</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Gender') }}</label>
                                <select name="gender" x-model="form.gender"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                                    <option value="">{{ __('staff_profiles.Select') }}</option>
                                    <option value="male" @selected(old('gender', $profile->gender) === 'male')>
                                        {{ __('staff_profiles.Male') }}</option>
                                    <option value="female" @selected(old('gender', $profile->gender) === 'female')>
                                        {{ __('staff_profiles.Female') }}</option>
                                    <option value="other" @selected(old('gender', $profile->gender) === 'other')>
                                        {{ __('staff_profiles.Other') }}</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Ethnicity') }}</label>
                                <input type="text" name="ethnicity" x-model="form.ethnicity"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Religious') }}</label>
                                <input type="text" name="religious" x-model="form.religious"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.NRC') }}
                                    <span class="text-red-500">*</span></label>
                                <input type="text" name="nrc" x-model="form.nrc" required autocomplete="off"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500"
                                    :class="errors.nrc ? 'field-error' : ''">
                                <p x-show="errors.nrc" x-text="errors.nrc"
                                    class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                @error('nrc')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.D.O.B') }}</label>
                                <input type="date" name="dob" x-model="form.dob"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Education') }}</label>
                                <input type="text" name="qualification" x-model="form.qualification"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                            </div>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Green card') }}</label>
                            <input type="text" name="green_card" x-model="form.green_card"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                        </div>
                    </div>

                    <!-- Step 3: Family Information -->
                    <div x-show="step === 3" x-cloak class="space-y-4">
                        <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                            <i class="fas fa-users text-amber-500"></i>
                            <span>{{ __('staff_profiles.Family Information') }}</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Father name') }}</label>
                                <input type="text" name="father_name" x-model="form.father_name"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Father\'s Ph no.') }}</label>
                                <input type="text" name="father_phone" x-model="form.father_phone"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Mother name') }}</label>
                                <input type="text" name="mother_name" x-model="form.mother_name"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Mother\'s Ph no.') }}</label>
                                <input type="text" name="mother_phone" x-model="form.mother_phone"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                            </div>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Emergency contact ph no.') }}</label>
                            <input type="text" name="emergency_contact" x-model="form.emergency_contact"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                        </div>
                    </div>

                    <!-- Step 4: Marital Status & In-School Relative -->
                    <div x-show="step === 4" x-cloak class="space-y-4">
                        <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                            <i class="fas fa-heart text-amber-500"></i>
                            <span>{{ __('staff_profiles.Marital Status & In-School Relative') }}</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Marital Status') }}</label>
                                <select name="marital_status" x-model="form.marital_status"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                                    <option value="">{{ __('staff_profiles.Select') }}</option>
                                    <option value="single" @selected(old('marital_status', $profile->marital_status) === 'single')>{{ __('staff_profiles.Single') }}</option>
                                    <option value="married" @selected(old('marital_status', $profile->marital_status) === 'married')>{{ __('staff_profiles.Married') }}
                                    </option>
                                    <option value="divorced" @selected(old('marital_status', $profile->marital_status) === 'divorced')>{{ __('staff_profiles.Divorced') }}
                                    </option>
                                    <option value="widowed" @selected(old('marital_status', $profile->marital_status) === 'widowed')>{{ __('staff_profiles.Widowed') }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Partner Name') }}</label>
                                <input type="text" name="partner_name" x-model="form.partner_name"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Partner\'s Ph no.') }}</label>
                                <input type="text" name="partner_phone" x-model="form.partner_phone"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.In-school Relative name') }}</label>
                                <input type="text" name="relative_name" x-model="form.relative_name"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                            </div>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Relationship') }}</label>
                            <input type="text" name="relative_relationship" x-model="form.relative_relationship"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                        </div>
                    </div>

                    <!-- Step 5: Physical & Medical Information -->
                    <div x-show="step === 5" x-cloak class="space-y-4">
                        <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                            <i class="fas fa-notes-medical text-amber-500"></i>
                            <span>{{ __('staff_profiles.Physical & Medical Information') }}</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Height (cm)') }}</label>
                                <input type="number" step="0.01" name="height" x-model="form.height"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Medicine allergy') }}</label>
                                <input type="text" name="medicine_allergy" x-model="form.medicine_allergy"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Food allergy') }}</label>
                                <input type="text" name="food_allergy" x-model="form.food_allergy"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                            </div>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Medical Directory') }}</label>
                            <textarea name="medical_directory" rows="3" x-model="form.medical_directory"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500"></textarea>
                        </div>
                    </div>

                    <!-- Step 6: Portal Registration -->
                    <div x-show="step === 6" x-cloak class="space-y-4">
                        <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                            <i class="fas fa-clipboard-check text-amber-500"></i>
                            <span>{{ __('staff_profiles.Portal Registration') }}</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Portal Email') }}
                                    <span class="text-red-500">*</span></label>
                                <input type="email" name="email" x-model="form.email" required autocomplete="email"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500"
                                    :class="errors.email ? 'field-error' : ''">
                                <p x-show="errors.email" x-text="errors.email"
                                    class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                @error('email')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Portal Password (leave blank to keep)') }}</label>
                                <input type="password" name="password" autocomplete="current-password"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500"
                                    placeholder="********">
                                @error('password')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}
                                </p>@enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Phone Number') }}
                                    <span class="text-red-500">*</span></label>
                                <input type="text" name="phone" x-model="form.phone" required autocomplete="tel"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500"
                                    :class="errors.phone ? 'field-error' : ''">
                                <p x-show="errors.phone" x-text="errors.phone"
                                    class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                @error('phone')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Status') }}</label>
                                <select name="status" x-model="form.status"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
                                    <option value="active">{{ __('staff_profiles.Active') }}</option>
                                    <option value="inactive">{{ __('staff_profiles.Inactive') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <label
                                class="inline-flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="is_active" x-model="form.is_active"
                                    :checked="form.is_active" value="1"
                                    class="rounded border-gray-300 dark:border-gray-700 text-amber-600 shadow-sm focus:ring-amber-500 dark:bg-gray-900 dark:text-gray-300">
                                <span class="ml-2">{{ __('staff_profiles.Allow portal login') }}</span>
                            </label>
                        </div>
                        <div
                            class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-4 text-sm text-gray-600 dark:text-gray-300">
                            {{ __('staff_profiles.Review the details and submit to update the staff profile and portal access.') }}
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-2">
                        <a href="{{ route('staff-profiles.index') }}"
                            class="w-full sm:w-auto px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg font-semibold text-center hover:bg-gray-200 dark:hover:bg-gray-600">{{ __('staff_profiles.Cancel') }}</a>
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <button type="button" @click="step = Math.max(1, step - 1)" :disabled="step === 1"
                                class="flex-1 sm:flex-none px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50">
                                <i class="fas fa-chevron-left mr-2"></i>{{ __('staff_profiles.Back') }}
                            </button>
                            <button type="button" x-show="step < total" @click="nextStep()"
                                class="flex-1 sm:flex-none px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg shadow-sm">
                                {{ __('staff_profiles.Next step') }} <i class="fas fa-chevron-right ml-2"></i>
                            </button>
                            <button type="submit" x-show="step === total"
                                class="flex-1 sm:flex-none px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg shadow-sm">
                                {{ __('staff_profiles.Update Staff') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function previewStaffPhoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('photo-preview').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</x-app-layout>