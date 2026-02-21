<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('student-profiles.index') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                <i class="fas fa-user-edit"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('student_profiles.Profiles') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('student_profiles.Edit Student') }}</h2>
            </div>
        </div>
    </x-slot>

    <!-- Include the reusable error modal component -->
    <x-alert-error />

    <style>[x-cloak]{display:none;}</style>
    <script src="{{ asset('js/form-validation.js') }}?v={{ time() }}"></script>

    <div class="py-6 sm:py-10" x-data="{
        step: 1,
        total: 7,
        form: {
            user_id: @js(old('user_id', $studentProfile->user_id)),
            name: @js(old('name', $studentProfile->user?->name)),
            phone: @js(old('phone', $studentProfile->user?->phone)),
            nrc: @js(old('nrc', $studentProfile->user?->nrc)),
            student_identifier: @js(old('student_identifier', $studentProfile->student_identifier)),
            status: @js(old('status', $studentProfile->status ?? 'active')),
            grade_id: @js(old('grade_id', $studentProfile->grade_id)),
            class_id: @js(old('class_id', $studentProfile->class_id)),
            starting_grade_at_school: @js(old('starting_grade_at_school', $studentProfile->starting_grade_at_school)),
            current_grade: @js(old('current_grade', $studentProfile->current_grade)),
            current_class: @js(old('current_class', $studentProfile->current_class)),
            guardian_teacher: @js(old('guardian_teacher', $studentProfile->guardian_teacher)),
            assistant_teacher: @js(old('assistant_teacher', $studentProfile->assistant_teacher)),
            date_of_joining: @js(old('date_of_joining', $studentProfile->date_of_joining?->format('Y-m-d'))),
            gender: @js(old('gender', $studentProfile->gender)),
            ethnicity: @js(old('ethnicity', $studentProfile->ethnicity)),
            religious: @js(old('religious', $studentProfile->religious)),
            dob: @js(old('dob', $studentProfile->dob?->format('Y-m-d'))),
            previous_school_name: @js(old('previous_school_name', $studentProfile->previous_school_name)),
            previous_school_address: @js(old('previous_school_address', $studentProfile->previous_school_address)),
            address: @js(old('address', $studentProfile->address)),
            father_name: @js(old('father_name', $studentProfile->father_name)),
            father_nrc: @js(old('father_nrc', $studentProfile->father_nrc)),
            father_religious: @js(old('father_religious', $studentProfile->father_religious)),
            father_phone_no: @js(old('father_phone_no', $studentProfile->father_phone_no)),
            father_occupation: @js(old('father_occupation', $studentProfile->father_occupation)),
            father_address: @js(old('father_address', $studentProfile->father_address)),
            mother_name: @js(old('mother_name', $studentProfile->mother_name)),
            mother_nrc: @js(old('mother_nrc', $studentProfile->mother_nrc)),
            mother_religious: @js(old('mother_religious', $studentProfile->mother_religious)),
            mother_phone_no: @js(old('mother_phone_no', $studentProfile->mother_phone_no)),
            mother_occupation: @js(old('mother_occupation', $studentProfile->mother_occupation)),
            mother_address: @js(old('mother_address', $studentProfile->mother_address)),
            emergency_contact_phone_no: @js(old('emergency_contact_phone_no', $studentProfile->emergency_contact_phone_no)),
            in_school_relative_name: @js(old('in_school_relative_name', $studentProfile->in_school_relative_name)),
            in_school_relative_grade: @js(old('in_school_relative_grade', $studentProfile->in_school_relative_grade)),
            in_school_relative_relationship: @js(old('in_school_relative_relationship', $studentProfile->in_school_relative_relationship)),
            blood_type: @js(old('blood_type', $studentProfile->blood_type)),
            weight: @js(old('weight', $studentProfile->weight)),
            height: @js(old('height', $studentProfile->height)),
            medicine_allergy: @js(old('medicine_allergy', $studentProfile->medicine_allergy)),
            food_allergy: @js(old('food_allergy', $studentProfile->food_allergy)),
            medical_directory: @js(old('medical_directory', $studentProfile->medical_directory)),
        },
        errors: {},
        
        nextStep() {
            // Step 1: Validate student name
            if (this.step === 1) {
                if (!this.form.name || this.form.name.trim() === '') {
                    this.errors = { name: 'Name is required' };
                    updateFieldErrorClasses(this.errors);
                    showNotification('Name is required', 'error');
                    return;
                }
            }
            
            // Step 2: Validate grade
            if (this.step === 2) {
                if (!this.form.grade_id || this.form.grade_id === '') {
                    this.errors = { grade_id: 'Grade is required' };
                    updateFieldErrorClasses(this.errors);
                    showNotification('Grade is required', 'error');
                    return;
                }
            }
            
            // Step 3: Validate all personal details fields
            if (this.step === 3) {
                this.errors = {};
                let hasError = false;
                let errorMessage = '';
                
                if (!this.form.gender || this.form.gender === '') {
                    this.errors.gender = 'Gender is required';
                    errorMessage = 'Gender is required';
                    hasError = true;
                }
                
                if (!this.form.ethnicity || this.form.ethnicity.trim() === '') {
                    this.errors.ethnicity = 'Ethnicity is required';
                    if (!errorMessage) errorMessage = 'Ethnicity is required';
                    hasError = true;
                }
                
                if (!this.form.religious || this.form.religious.trim() === '') {
                    this.errors.religious = 'Religious is required';
                    if (!errorMessage) errorMessage = 'Religious is required';
                    hasError = true;
                }
                
                if (!this.form.dob || this.form.dob === '') {
                    this.errors.dob = 'DOB is required';
                    if (!errorMessage) errorMessage = 'DOB is required';
                    hasError = true;
                }
                
                if (!this.form.address || this.form.address.trim() === '') {
                    this.errors.address = 'Address is required';
                    if (!errorMessage) errorMessage = 'Address is required';
                    hasError = true;
                }
                
                if (hasError) {
                    updateFieldErrorClasses(this.errors);
                    showNotification(errorMessage, 'error');
                    return;
                }
            }
            
            // Step 5: Validate family & emergency fields (except In-school Relative fields and occupations)
            if (this.step === 5) {
                this.errors = {};
                let hasError = false;
                let errorMessage = '';
                
                if (!this.form.father_name || this.form.father_name.trim() === '') {
                    this.errors.father_name = 'Father Name is required';
                    errorMessage = 'Father Name is required';
                    hasError = true;
                }
                
                if (!this.form.father_nrc || this.form.father_nrc.trim() === '') {
                    this.errors.father_nrc = 'Father NRC is required';
                    if (!errorMessage) errorMessage = 'Father NRC is required';
                    hasError = true;
                }
                
                if (!this.form.father_phone_no || this.form.father_phone_no.trim() === '') {
                    this.errors.father_phone_no = 'Father Phone No. is required';
                    if (!errorMessage) errorMessage = 'Father Phone No. is required';
                    hasError = true;
                }
                
                if (!this.form.mother_name || this.form.mother_name.trim() === '') {
                    this.errors.mother_name = 'Mother Name is required';
                    if (!errorMessage) errorMessage = 'Mother Name is required';
                    hasError = true;
                }
                
                if (!this.form.mother_nrc || this.form.mother_nrc.trim() === '') {
                    this.errors.mother_nrc = 'Mother NRC is required';
                    if (!errorMessage) errorMessage = 'Mother NRC is required';
                    hasError = true;
                }
                
                if (!this.form.mother_phone_no || this.form.mother_phone_no.trim() === '') {
                    this.errors.mother_phone_no = 'Mother Phone No. is required';
                    if (!errorMessage) errorMessage = 'Mother Phone No. is required';
                    hasError = true;
                }
                
                if (!this.form.emergency_contact_phone_no || this.form.emergency_contact_phone_no.trim() === '') {
                    this.errors.emergency_contact_phone_no = 'Emergency Contact Phone is required';
                    if (!errorMessage) errorMessage = 'Emergency Contact Phone is required';
                    hasError = true;
                }
                
                if (hasError) {
                    updateFieldErrorClasses(this.errors);
                    showNotification(errorMessage, 'error');
                    return;
                }
            }
            
            // Clear errors and proceed to next step
            this.errors = {};
            this.step = Math.min(this.total, this.step + 1);
        }
    }">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-4 sm:p-5">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                        <i class="fas fa-user-graduate text-indigo-500"></i>
                        <span>{{ __('student_profiles.Edit Student Wizard') }}</span>
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
                <form method="POST" action="{{ route('student-profiles.update', $studentProfile) }}" class="p-6 sm:p-8 space-y-6" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="user_id" value="{{ $studentProfile->user_id }}">

                    <!-- Step 1: Portal Account & ID -->
                    <div x-show="step === 1" x-cloak class="space-y-4">
                        <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                            <i class="fas fa-id-card-alt text-indigo-500"></i>
                            <span>{{ __('student_profiles.Portal Account & Student ID') }}</span>
                        </div>

                        <!-- Student Photo Upload -->
                        <div class="flex flex-col items-center gap-4 p-6 bg-gray-50 dark:bg-gray-900/50 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700">
                            <div x-data="{ photoPreview: '{{ avatar_url($studentProfile->photo_path, 'student') }}', photoName: '' }">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="relative">
                                        <div class="w-32 h-32 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                            <template x-if="!photoPreview">
                                                <i class="fas fa-user text-4xl text-gray-400 dark:text-gray-500"></i>
                                            </template>
                                            <template x-if="photoPreview">
                                                <img :src="photoPreview" alt="Student Photo" class="w-full h-full object-cover">
                                            </template>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <label for="photo" class="cursor-pointer inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                                            <i class="fas fa-camera mr-2"></i>
                                            <span x-text="photoName ? 'Change Photo' : 'Upload Photo'"></span>
                                        </label>
                                        <input type="file" id="photo" name="photo" accept="image/*" class="hidden"
                                               @change="
                                                   const file = $event.target.files[0];
                                                   if (file) {
                                                       photoName = file.name;
                                                       const reader = new FileReader();
                                                       reader.onload = (e) => { photoPreview = e.target.result; };
                                                       reader.readAsDataURL(file);
                                                   }
                                               ">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ __('student_profiles.JPG, PNG or GIF (MAX. 2MB)') }}</p>
                                        <p x-show="photoName" x-text="photoName" class="text-xs text-gray-600 dark:text-gray-400 mt-1"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Student Name') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="form.name" autocomplete="name" 
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                       :class="errors.name ? 'field-error' : ''">
                                <p x-show="errors.name" x-text="errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Phone') }}</label>
                                <input type="text" name="phone" x-model="form.phone" autocomplete="tel" 
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                @error('phone')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.NRC / ID') }}</label>
                                <input type="text" name="nrc" x-model="form.nrc" autocomplete="off" 
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Student Identifier') }}</label>
                                <input type="text" name="student_identifier" x-model="form.student_identifier" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500" placeholder="STD-XXXX (auto if blank)">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Profile Status') }}</label>
                            <select name="status" x-model="form.status" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="active">{{ __('student_profiles.Active') }}</option>
                                <option value="inactive">{{ __('student_profiles.Inactive') }}</option>
                            </select>
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
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Grade') }} <span class="text-red-500">*</span></label>
                                <select name="grade_id" x-model="form.grade_id" 
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="errors.grade_id ? 'field-error' : ''">
                                    <option value="">{{ __('student_profiles.Select grade') }}</option>
                                    @foreach($grades as $grade)
                                        <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                    @endforeach
                                </select>
                                <p x-show="errors.grade_id" x-text="errors.grade_id" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Class') }}</label>
                                <select name="class_id" x-model="form.class_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">{{ __('student_profiles.Select class') }}</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
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
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Gender') }} <span class="text-red-500">*</span></label>
                                <select name="gender" x-model="form.gender" 
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="errors.gender ? 'field-error' : ''">
                                    <option value="">{{ __('student_profiles.Select') }}</option>
                                    <option value="male" @selected(old('gender', $studentProfile->gender) === 'male')>{{ __('student_profiles.Male') }}</option>
                                    <option value="female" @selected(old('gender', $studentProfile->gender) === 'female')>{{ __('student_profiles.Female') }}</option>
                                    <option value="other" @selected(old('gender', $studentProfile->gender) === 'other')>{{ __('student_profiles.Other') }}</option>
                                </select>
                                <p x-show="errors.gender" x-text="errors.gender" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Ethnicity') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="ethnicity" x-model="form.ethnicity" 
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                       :class="errors.ethnicity ? 'field-error' : ''">
                                <p x-show="errors.ethnicity" x-text="errors.ethnicity" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Religious') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="religious" x-model="form.religious" 
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                       :class="errors.religious ? 'field-error' : ''">
                                <p x-show="errors.religious" x-text="errors.religious" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.DOB') }} <span class="text-red-500">*</span></label>
                                <input type="date" name="dob" x-model="form.dob" 
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                       :class="errors.dob ? 'field-error' : ''">
                                <p x-show="errors.dob" x-text="errors.dob" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Address') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="address" x-model="form.address" 
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                       :class="errors.address ? 'field-error' : ''">
                                <p x-show="errors.address" x-text="errors.address" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
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
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Father Name') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="father_name" x-model="form.father_name" 
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                       :class="errors.father_name ? 'field-error' : ''">
                                <p x-show="errors.father_name" x-text="errors.father_name" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Father NRC') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="father_nrc" x-model="form.father_nrc" 
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                       :class="errors.father_nrc ? 'field-error' : ''">
                                <p x-show="errors.father_nrc" x-text="errors.father_nrc" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Father Phone No.') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="father_phone_no" x-model="form.father_phone_no" 
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                       :class="errors.father_phone_no ? 'field-error' : ''">
                                <p x-show="errors.father_phone_no" x-text="errors.father_phone_no" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Father Occupation') }}</label>
                                <input type="text" name="father_occupation" x-model="form.father_occupation" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Mother Name') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="mother_name" x-model="form.mother_name" 
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                       :class="errors.mother_name ? 'field-error' : ''">
                                <p x-show="errors.mother_name" x-text="errors.mother_name" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Mother NRC') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="mother_nrc" x-model="form.mother_nrc" 
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                       :class="errors.mother_nrc ? 'field-error' : ''">
                                <p x-show="errors.mother_nrc" x-text="errors.mother_nrc" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Mother Phone No.') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="mother_phone_no" x-model="form.mother_phone_no" 
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                       :class="errors.mother_phone_no ? 'field-error' : ''">
                                <p x-show="errors.mother_phone_no" x-text="errors.mother_phone_no" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Mother Occupation') }}</label>
                                <input type="text" name="mother_occupation" x-model="form.mother_occupation" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Emergency Contact Phone') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="emergency_contact_phone_no" x-model="form.emergency_contact_phone_no" 
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                       :class="errors.emergency_contact_phone_no ? 'field-error' : ''">
                                <p x-show="errors.emergency_contact_phone_no" x-text="errors.emergency_contact_phone_no" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
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

                    <!-- Step 7: Guardian Information -->
                    <div x-show="step === 7" x-cloak class="space-y-4">
                        <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                            <i class="fas fa-user-shield text-indigo-500"></i>
                            <span>{{ __('student_profiles.Guardian Information') }}</span>
                        </div>
                        
                        @if($studentProfile->guardians && $studentProfile->guardians->count() > 0)
                            <div class="space-y-3">
                                @foreach($studentProfile->guardians as $guardian)
                                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex items-start gap-3 flex-1">
                                                <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center flex-shrink-0">
                                                    <i class="fas fa-user-shield text-indigo-600 dark:text-indigo-400"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $guardian->user->name }}</h4>
                                                    <div class="mt-2 space-y-1">
                                                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                                            <i class="fas fa-envelope w-4 text-gray-400"></i>
                                                            <span>{{ $guardian->user->email }}</span>
                                                        </div>
                                                        @if($guardian->user->phone)
                                                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                                                <i class="fas fa-phone w-4 text-gray-400"></i>
                                                                <span>{{ $guardian->user->phone }}</span>
                                                            </div>
                                                        @endif
                                                        @if($guardian->pivot && $guardian->pivot->relationship)
                                                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                                                <i class="fas fa-link w-4 text-gray-400"></i>
                                                                <span class="capitalize">{{ $guardian->pivot->relationship }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-6 text-center">
                                <i class="fas fa-user-shield text-3xl text-gray-400 dark:text-gray-600 mb-2"></i>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('student_profiles.No guardian linked to this student') }}</p>
                            </div>
                        @endif
                        
                        <div class="rounded-lg border border-blue-200 dark:border-blue-700 bg-blue-50 dark:bg-blue-900/20 p-4">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mt-0.5"></i>
                                <div class="text-sm text-blue-800 dark:text-blue-300">
                                    <p class="font-semibold mb-1">{{ __('student_profiles.Guardian Management') }}</p>
                                    <p>{{ __('student_profiles.Guardian information is managed separately. To add or modify guardians, please use the Guardian Management section.') }}</p>
                                </div>
                            </div>
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
                                {{ __('student_profiles.Update Student') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Define showNotification to dispatch Alpine.js event to the alert-error component
        window.showNotification = function(message, type) {
            window.dispatchEvent(new CustomEvent('show-error', {
                detail: { message: message }
            }));
        };
        
        // Show backend validation errors on page load
        @if($errors->any())
            document.addEventListener('DOMContentLoaded', function() {
                const firstError = @json($errors->first());
                showNotification(firstError, 'error');
            });
        @endif
    </script>
</x-app-layout>
