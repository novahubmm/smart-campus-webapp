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
                    form: {
                        name: @js(old('name')),
                        phone: @js(old('phone')),
                        nrc: @js(old('nrc')),
                        student_identifier: @js(old('student_identifier')),
                        status: @js(old('status', 'active')),
                        grade_id: @js(old('grade_id')),
                        class_id: @js(old('class_id')),
                        starting_grade_at_school: @js(old('starting_grade_at_school')),
                        previous_grade: @js(old('previous_grade')),
                        previous_class: @js(old('previous_class')),
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
                        father_religious: @js(old('father_religious')),
                        father_phone_no: @js(old('father_phone_no')),
                        father_occupation: @js(old('father_occupation')),
                        father_address: @js(old('father_address')),
                        mother_name: @js(old('mother_name')),
                        mother_nrc: @js(old('mother_nrc')),
                        mother_religious: @js(old('mother_religious')),
                        mother_phone_no: @js(old('mother_phone_no')),
                        mother_occupation: @js(old('mother_occupation')),
                        mother_address: @js(old('mother_address')),
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
                        guardian_name: @js(old('guardian_name')),
                        guardian_phone: @js(old('guardian_phone')),
                        guardian_email: @js(old('guardian_email')),
                        existing_guardian_id: @js(old('existing_guardian_id')),
                    },
                    useExistingGuardian: 'false',
                    errors: {},
                    
                    nextStep() {
                        // Step 1: Validate student name
                        if (this.step === 1) {
                            if (!this.form.name || this.form.name.trim() === '') {
                                this.errors = { name: 'Name is required' };
                                updateFieldErrorClasses(this.errors);
                                return;
                            }
                        }
                        
                        // Step 2: Validate grade
                        if (this.step === 2) {
                            if (!this.form.grade_id || this.form.grade_id === '') {
                                this.errors = { grade_id: 'Grade is required' };
                                updateFieldErrorClasses(this.errors);
                                return;
                            }
                        }
                        
                        // Step 3: Validate all personal details fields
                        if (this.step === 3) {
                            this.errors = {};
                            let hasError = false;
                            
                            if (!this.form.gender || this.form.gender === '') {
                                this.errors.gender = 'Gender is required';
                                hasError = true;
                            }
                            
                            if (!this.form.ethnicity || this.form.ethnicity.trim() === '') {
                                this.errors.ethnicity = 'Ethnicity is required';
                                hasError = true;
                            }
                            
                            if (!this.form.religious || this.form.religious.trim() === '') {
                                this.errors.religious = 'Religious is required';
                                hasError = true;
                            }
                            
                            if (!this.form.dob || this.form.dob === '') {
                                this.errors.dob = 'DOB is required';
                                hasError = true;
                            }
                            
                            if (!this.form.address || this.form.address.trim() === '') {
                                this.errors.address = 'Address is required';
                                hasError = true;
                            }
                            
                            if (hasError) {
                                updateFieldErrorClasses(this.errors);
                                return;
                            }
                        }
                        
                        // Step 5: Validate family & emergency fields (except In-school Relative fields and occupations)
                        if (this.step === 5) {
                            this.errors = {};
                            let hasError = false;
                            
                            if (!this.form.father_name || this.form.father_name.trim() === '') {
                                this.errors.father_name = 'Father Name is required';
                                hasError = true;
                            }
                            
                            if (!this.form.father_nrc || this.form.father_nrc.trim() === '') {
                                this.errors.father_nrc = 'Father NRC is required';
                                hasError = true;
                            }
                            
                            if (!this.form.father_phone_no || this.form.father_phone_no.trim() === '') {
                                this.errors.father_phone_no = 'Father Phone No. is required';
                                hasError = true;
                            }
                            
                            if (!this.form.mother_name || this.form.mother_name.trim() === '') {
                                this.errors.mother_name = 'Mother Name is required';
                                hasError = true;
                            }
                            
                            if (!this.form.mother_nrc || this.form.mother_nrc.trim() === '') {
                                this.errors.mother_nrc = 'Mother NRC is required';
                                hasError = true;
                            }
                            
                            if (!this.form.mother_phone_no || this.form.mother_phone_no.trim() === '') {
                                this.errors.mother_phone_no = 'Mother Phone No. is required';
                                hasError = true;
                            }
                            
                            if (!this.form.emergency_contact_phone_no || this.form.emergency_contact_phone_no.trim() === '') {
                                this.errors.emergency_contact_phone_no = 'Emergency Contact Phone is required';
                                hasError = true;
                            }
                            
                            if (hasError) {
                                updateFieldErrorClasses(this.errors);
                                return;
                            }
                        }
                        
                        // Step 7: Validation logic moved to submitForm()
                        
                        // Clear errors and proceed to next step
                        this.errors = {};
                        this.step = Math.min(this.total, this.step + 1);
                    },

                    submitForm() {
                        // Validate step 7 before submission
                        this.errors = {};
                        let hasError = false;
                        
                        if (this.useExistingGuardian === 'true') {
                            // Validate existing guardian selection
                            if (!this.form.existing_guardian_id || this.form.existing_guardian_id === '') {
                                this.errors.existing_guardian_id = '{{ __('student_profiles.Please select a guardian') }}';
                                hasError = true;
                            }
                        } else {
                            // Validate new guardian fields
                            if (!this.form.guardian_name || this.form.guardian_name.trim() === '') {
                                this.errors.guardian_name = '{{ __('student_profiles.Guardian Name is required') }}';
                                hasError = true;
                            }
                            
                            if (!this.form.guardian_phone || this.form.guardian_phone.trim() === '') {
                                this.errors.guardian_phone = '{{ __('student_profiles.Guardian Phone No. is required') }}';
                                hasError = true;
                            }
                            
                            if (!this.form.guardian_email || this.form.guardian_email.trim() === '') {
                                this.errors.guardian_email = '{{ __('student_profiles.Guardian Email is required') }}';
                                hasError = true;
                            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.guardian_email)) {
                                this.errors.guardian_email = '{{ __('student_profiles.Please enter a valid email address') }}';
                                hasError = true;
                            }
                        }
                        
                        if (hasError) {
                            updateFieldErrorClasses(this.errors);
                            return;
                        }

                        // If all valid, submit the form
                        this.$el.closest('form').submit();
                    }
                }">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
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
                            <form method="POST" action="{{ route('student-profiles.store') }}" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
                                @csrf

                                <!-- Step 1: Portal Account & ID -->
                                <div x-show="step === 1" x-cloak class="space-y-4">
                                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                        <i class="fas fa-id-card-alt text-indigo-500"></i>
                                        <span>{{ __('student_profiles.Portal Account & Student ID') }}</span>
                                    </div>

                                    <!-- Student Photo Upload -->
                                    <div class="flex flex-col items-center gap-4 p-6 bg-gray-50 dark:bg-gray-900/50 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700">
                                        <div x-data="{ 
                                            photoPreview: null, 
                                            photoName: '',
                                            showCamera: false,
                                            stream: null,
                                            error: null,
                                            
                                            async startCamera() {
                                                this.error = null;
                                                this.showCamera = true;
                                                try {
                                                    this.stream = await navigator.mediaDevices.getUserMedia({ video: true });
                                                    this.$refs.video.srcObject = this.stream;
                                                } catch (err) {
                                                    console.error('Error accessing camera:', err);
                                                    this.error = 'Could not access camera. Please ensure permissions are granted.';
                                                    this.stopCamera();
                                                }
                                            },
                                            
                                            stopCamera() {
                                                if (this.stream) {
                                                    this.stream.getTracks().forEach(track => track.stop());
                                                    this.stream = null;
                                                }
                                                this.showCamera = false;
                                            },
                                            
                                            takePhoto() {
                                                const video = this.$refs.video;
                                                if (!video.videoWidth) return;
                                                
                                                const canvas = document.createElement('canvas');
                                                canvas.width = video.videoWidth;
                                                canvas.height = video.videoHeight;
                                                canvas.getContext('2d').drawImage(video, 0, 0);
                                                
                                                // Get blob and create file
                                                canvas.toBlob((blob) => {
                                                    const file = new File([blob], 'webcam_photo.jpg', { type: 'image/jpeg' });
                                                    
                                                    // Create a DataTransfer object to assign to the file input
                                                    const dataTransfer = new DataTransfer();
                                                    dataTransfer.items.add(file);
                                                    document.getElementById('photo').files = dataTransfer.files;
                                                    
                                                    this.photoName = 'webcam_photo.jpg';
                                                    this.photoPreview = canvas.toDataURL('image/jpeg');
                                                    this.stopCamera();
                                                }, 'image/jpeg', 0.9);
                                            }
                                        }" class="w-full">
                                            <div class="flex flex-col items-center gap-3 w-full">
                                                <!-- Normal Photo Preview / Upload -->
                                                <div x-show="!showCamera" class="flex flex-col items-center gap-3">
                                                    <div class="relative">
                                                        <div class="w-32 h-32 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700 flex items-center justify-center border-4 border-white dark:border-gray-800 shadow-sm">
                                                            <template x-if="!photoPreview">
                                                                <i class="fas fa-user text-4xl text-gray-400 dark:text-gray-500"></i>
                                                            </template>
                                                            <template x-if="photoPreview">
                                                                <img :src="photoPreview" alt="Student Photo" class="w-full h-full object-cover">
                                                            </template>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="flex flex-wrap justify-center gap-2 mt-2">
                                                        <label for="photo" class="cursor-pointer inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                                                            <i class="fas fa-upload mr-2"></i>
                                                            <span x-text="photoName ? 'Change File' : 'Upload File'"></span>
                                                        </label>
                                                        <button type="button" @click="startCamera()" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 text-sm font-medium rounded-lg transition-colors shadow-sm">
                                                            <i class="fas fa-camera mr-2"></i> {{ __('Take Picture') }}
                                                        </button>
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
                                                    </div>
                                                    <p x-show="error" x-text="error" class="text-sm text-red-600 dark:text-red-400 mt-1"></p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('student_profiles.JPG, PNG or GIF (MAX. 2MB)') }}</p>
                                                    <p x-show="photoName" x-text="photoName" class="text-xs text-indigo-600 dark:text-indigo-400 mt-1 font-medium"></p>
                                                </div>
                                                
                                                <!-- Camera Interface -->
                                                <div x-show="showCamera" x-cloak class="w-full sm:max-w-sm flex flex-col items-center">
                                                    <div class="relative w-full aspect-[4/3] rounded-lg overflow-hidden bg-black mb-3 border-2 border-indigo-500 shadow-md">
                                                        <video x-ref="video" class="w-full h-full object-cover" autoplay playsinline></video>
                                                        <!-- Oval Guide -->
                                                        <div class="absolute inset-0 pointer-events-none flex items-center justify-center p-4">
                                                            <div class="w-3/4 h-[80%] border-2 border-white/50 rounded-full"></div>
                                                        </div>
                                                    </div>
                                                    <div class="flex justify-center gap-3 w-full">
                                                        <button type="button" @click="stopCamera()" class="flex-1 py-2 px-4 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-sm font-medium rounded-lg transition-colors">
                                                            {{ __('student_profiles.Cancel') }}
                                                        </button>
                                                        <button type="button" @click="takePhoto()" class="flex-1 py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                                                            <i class="fas fa-camera mr-2"></i> {{ __('Capture') }}
                                                        </button>
                                                    </div>
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
                                            <select name="grade_id" x-model="form.grade_id" @change="form.class_id = ''"
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
                                            <select name="class_id" x-model="form.class_id" :disabled="!form.grade_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                                <option value="">{{ __('student_profiles.Select class') }}</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->id }}" x-show="form.grade_id === '{{ $class->grade_id }}'">{{ $class->name }}</option>
                                                @endforeach
                                            </select>
                                            <p x-show="!form.grade_id" class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('student_profiles.Select a grade first') }}</p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Starting Grade at School') }}</label>
                                            <input type="text" name="starting_grade_at_school" x-model="form.starting_grade_at_school" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
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
                                                <option value="male" @selected(old('gender') === 'male')>{{ __('student_profiles.Male') }}</option>
                                                <option value="female" @selected(old('gender') === 'female')>{{ __('student_profiles.Female') }}</option>
                                                <option value="other" @selected(old('gender') === 'other')>{{ __('student_profiles.Other') }}</option>
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
                                    
                                    <!-- Choose Guardian Type -->
                                    <div class="flex items-center gap-6 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio" name="guardian_type" value="false" x-model="useExistingGuardian" checked class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700">
                                            <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('student_profiles.Create New Guardian') }}</span>
                                        </label>
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio" name="guardian_type" value="true" x-model="useExistingGuardian" class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700">
                                            <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('student_profiles.Link Existing Guardian') }}</span>
                                        </label>
                                    </div>
                                    
                                    <!-- Existing Guardian Selection -->
                                    <div x-show="useExistingGuardian === 'true'" x-cloak>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Select Existing Guardian') }} <span class="text-red-500">*</span></label>
                                        <select name="existing_guardian_id" x-model="form.existing_guardian_id"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                                :class="errors.existing_guardian_id ? 'field-error' : ''">
                                            <option value="">{{ __('student_profiles.Select a guardian') }}</option>
                                            @foreach($guardians as $guardian)
                                                <option value="{{ $guardian->id }}">
                                                    {{ $guardian->user->name }} — {{ $guardian->user->email }} — {{ $guardian->user->phone }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <p x-show="errors.existing_guardian_id" x-text="errors.existing_guardian_id" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                        
                                        <div class="mt-4 rounded-lg border border-blue-200 dark:border-blue-700 bg-blue-50 dark:bg-blue-900/20 p-4">
                                            <div class="flex items-start gap-3">
                                                <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mt-0.5"></i>
                                                <div class="text-sm text-blue-800 dark:text-blue-300">
                                                    <p class="font-semibold mb-1">{{ __('student_profiles.Link Existing Guardian') }}</p>
                                                    <p>{{ __('student_profiles.The selected guardian will be linked to this student. No new account will be created.') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- New Guardian Form -->
                                    <div x-show="useExistingGuardian === 'false'" x-cloak>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Guardian Name') }} <span class="text-red-500">*</span></label>
                                                <input type="text" name="guardian_name" x-model="form.guardian_name" 
                                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                                       :class="errors.guardian_name ? 'field-error' : ''">
                                                <p x-show="errors.guardian_name" x-text="errors.guardian_name" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Guardian Phone No.') }} <span class="text-red-500">*</span></label>
                                                <input type="text" name="guardian_phone" x-model="form.guardian_phone" 
                                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                                       :class="errors.guardian_phone ? 'field-error' : ''">
                                                <p x-show="errors.guardian_phone" x-text="errors.guardian_phone" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Guardian Email') }} <span class="text-red-500">*</span></label>
                                                <input type="email" name="guardian_email" x-model="form.guardian_email" 
                                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                                       :class="errors.guardian_email ? 'field-error' : ''">
                                                <p x-show="errors.guardian_email" x-text="errors.guardian_email" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Guardian Password') }}</label>
                                                <input type="text" name="guardian_password" value="12345678" readonly
                                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500 bg-gray-100 text-gray-600 cursor-not-allowed">
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('student_profiles.Default password: 12345678') }}</p>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-4 rounded-lg border border-blue-200 dark:border-blue-700 bg-blue-50 dark:bg-blue-900/20 p-4">
                                            <div class="flex items-start gap-3">
                                                <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mt-0.5"></i>
                                                <div class="text-sm text-blue-800 dark:text-blue-300">
                                                    <p class="font-semibold mb-1">{{ __('student_profiles.Guardian Account Creation') }}</p>
                                                    <p>{{ __('student_profiles.A guardian account will be created with the provided information. The default password is 12345678 and should be changed after first login.') }}</p>
                                                </div>
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
                                        <button type="button" @click="submitForm()" x-show="step === total" class="flex-1 sm:flex-none px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-sm">
                                            {{ __('student_profiles.Create Student') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

    <script>
        function showNotification(message, type) {
            // Create modal overlay
            const overlay = document.createElement('div');
            overlay.className = 'fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4';
            overlay.style.animation = 'fadeIn 0.2s ease-in-out';
            
            // Create modal content - more compact design
            const modal = document.createElement('div');
            modal.className = 'bg-gray-800 rounded-xl shadow-2xl max-w-lg w-full p-6';
            modal.style.animation = 'slideIn 0.3s ease-out';
            
            // Create modal header with icon and title in one row
            const header = document.createElement('div');
            header.className = 'flex items-center gap-3 mb-4';
            
            const icon = document.createElement('div');
            icon.className = `w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 ${type === 'success' ? 'bg-green-600' : 'bg-red-900'}`;
            icon.innerHTML = '<i class="fas fa-exclamation-circle text-white text-xl"></i>';
            
            const title = document.createElement('h3');
            title.className = 'text-xl font-semibold text-white';
            title.textContent = type === 'success' ? 'Success' : 'Error';
            
            header.appendChild(icon);
            header.appendChild(title);
            
            // Create message
            const messageEl = document.createElement('p');
            messageEl.className = 'text-gray-300 text-sm leading-relaxed mb-6';
            messageEl.textContent = message;
            
            // Create button container aligned to right
            const buttonContainer = document.createElement('div');
            buttonContainer.className = 'flex justify-end';
            
            // Create OK button
            const button = document.createElement('button');
            button.className = 'bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 px-8 rounded-lg transition-colors';
            button.textContent = 'OK';
            button.onclick = () => overlay.remove();
            
            buttonContainer.appendChild(button);
            
            // Assemble modal
            modal.appendChild(header);
            modal.appendChild(messageEl);
            modal.appendChild(buttonContainer);
            overlay.appendChild(modal);
            
            // Add to body
            document.body.appendChild(overlay);
            
            // Close on overlay click
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    overlay.remove();
                }
            });
            
            // Close on Escape key
            const escapeHandler = (e) => {
                if (e.key === 'Escape') {
                    overlay.remove();
                    document.removeEventListener('keydown', escapeHandler);
                }
            };
            document.addEventListener('keydown', escapeHandler);
        }
        
        // Add animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes slideIn {
                from { transform: translateY(-20px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
        
        // Show backend validation errors on page load
        @if($errors->any())
            document.addEventListener('DOMContentLoaded', function() {
                const firstError = @json($errors->first());
                showNotification(firstError, 'error');
            });
        @endif
    </script>
            </x-app-layout>
