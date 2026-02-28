<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('staff-profiles.index') }}"
                class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <span
                class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg">
                <i class="fas fa-user-plus"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('staff_profiles.Profiles') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('staff_profiles.Create Staff') }}</h2>
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
            name: @js(old('name', '')),
            email: @js(old('email', '')),
            phone: @js(old('phone', '')),
            nrc: @js(old('nrc', '')),
            is_active: @js((bool) old('is_active', true)),
            status: @js(old('status', 'active')),
            employee_id: @js(old('employee_id', '')),
            position: @js(old('position', '')),
            department_id: @js(old('department_id')),
            hire_date: @js(old('hire_date', '')),
            basic_salary: @js(old('basic_salary', '')),
            phone_no: @js(old('phone_no', '')),
            address: @js(old('address', '')),
            gender: @js(old('gender', '')),
            ethnicity: @js(old('ethnicity', '')),
            religious: @js(old('religious', '')),
            dob: @js(old('dob', '')),
            qualification: @js(old('qualification', '')),
            green_card: @js(old('green_card', '')),
            father_name: @js(old('father_name', '')),
            father_phone: @js(old('father_phone', '')),
            mother_name: @js(old('mother_name', '')),
            mother_phone: @js(old('mother_phone', '')),
            emergency_contact: @js(old('emergency_contact', '')),
            marital_status: @js(old('marital_status', '')),
            partner_name: @js(old('partner_name', '')),
            partner_phone: @js(old('partner_phone', '')),
            relative_name: @js(old('relative_name', '')),
            relative_relationship: @js(old('relative_relationship', '')),
            height: @js(old('height', '')),
            medicine_allergy: @js(old('medicine_allergy', '')),
            food_allergy: @js(old('food_allergy', '')),
            medical_directory: @js(old('medical_directory', '')),
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div
                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-4 sm:p-5">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                        <i class="fas fa-user-cog text-amber-500"></i>
                        <span>{{ __('staff_profiles.Add New Staff') }}</span>
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
                <form method="POST" action="{{ route('staff-profiles.store') }}" class="p-6 sm:p-8 space-y-6"
                    enctype="multipart/form-data">
                    @csrf

                    <!-- Step 1: Basic Information -->
                    <div x-show="step === 1" x-cloak class="space-y-4">
                        <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                            <i class="fas fa-id-badge text-amber-500"></i>
                            <span>{{ __('staff_profiles.Basic Information') }}</span>
                        </div>
                        <!-- Staff Photo Upload -->
                        <div class="flex flex-col items-center gap-4 p-6 bg-gray-50 dark:bg-gray-900/50 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700 mb-4">
                            <div x-data="{ 
                                photoPreview: null, 
                                photoName: '',
                                showCamera: false,
                                stream: null,
                                error: null,
                                showPermissionModal: false,
                                
                                async startCamera() {
                                    this.error = null;
                                    this.showCamera = true;
                                    try {
                                        this.stream = await navigator.mediaDevices.getUserMedia({ video: true });
                                        this.$refs.video.srcObject = this.stream;
                                    } catch (err) {
                                        console.error('Error accessing camera:', err);
                                        this.showCamera = false;
                                        
                                        // Show permission modal for permission denied errors
                                        if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                                            this.showPermissionModal = true;
                                        } else if (err.name === 'NotFoundError') {
                                            this.error = '{{ __('staff_profiles.No camera found on this device.') }}';
                                        } else {
                                            this.error = '{{ __('staff_profiles.Could not access camera. Please try again or upload a file.') }}';
                                        }
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
                                                    <img :src="photoPreview" alt="Staff Photo" class="w-full h-full object-cover">
                                                </template>
                                            </div>
                                        </div>
                                        
                                        <div class="flex flex-wrap justify-center gap-2 mt-2">
                                            <label for="photo" class="cursor-pointer inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
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
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('staff_profiles.JPG, PNG or GIF (MAX. 2MB)') }}</p>
                                        <p x-show="photoName" x-text="photoName" class="text-xs text-amber-600 dark:text-amber-400 mt-1 font-medium"></p>
                                        @error('photo')<p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                    </div>
                                    
                                    <!-- Camera Interface -->
                                    <div x-show="showCamera" x-cloak class="w-full sm:max-w-sm flex flex-col items-center">
                                        <div class="relative w-full aspect-[4/3] rounded-lg overflow-hidden bg-black mb-3 border-2 border-amber-500 shadow-md">
                                            <video x-ref="video" class="w-full h-full object-cover" autoplay playsinline></video>
                                            <!-- Oval Guide -->
                                            <div class="absolute inset-0 pointer-events-none flex items-center justify-center p-4">
                                                <div class="w-3/4 h-[80%] border-2 border-white/50 rounded-full"></div>
                                            </div>
                                        </div>
                                        <div class="flex justify-center gap-3 w-full">
                                            <button type="button" @click="stopCamera()" class="flex-1 py-2 px-4 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-sm font-medium rounded-lg transition-colors">
                                                {{ __('staff_profiles.Cancel') }}
                                            </button>
                                            <button type="button" @click="takePhoto()" class="flex-1 py-2 px-4 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                                                <i class="fas fa-camera mr-2"></i> {{ __('Capture') }}
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Camera Permission Modal -->
                                    <div x-show="showPermissionModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showPermissionModal = false">
                                        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
                                        <div class="flex min-h-full items-center justify-center p-4">
                                            <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-md shadow-2xl" @click.stop>
                                                <!-- Modal Header -->
                                                <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                                            <i class="fas fa-camera text-amber-600 dark:text-amber-400"></i>
                                                        </div>
                                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Camera Permission Required</h3>
                                                    </div>
                                                    <button type="button" @click="showPermissionModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                
                                                <!-- Modal Body -->
                                                <div class="p-6 space-y-4">
                                                    <p class="text-gray-700 dark:text-gray-300">To take a photo, you need to allow camera access in your browser.</p>
                                                    
                                                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                                                        <p class="text-sm font-semibold text-amber-900 dark:text-amber-200 mb-2"><i class="fas fa-camera mr-1"></i>How to enable camera:</p>
                                                        <ol class="text-sm text-amber-800 dark:text-amber-300 space-y-2 list-decimal list-inside">
                                                            <li>Look for the camera icon <i class="fas fa-video text-xs"></i> or lock icon <i class="fas fa-lock text-xs"></i> in your browser's address bar</li>
                                                            <li>Click on it and select "Allow" for camera permissions</li>
                                                            <li>Click "Try Again" button below</li>
                                                        </ol>
                                                    </div>
                                                    
                                                    <div class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3">
                                                        <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                                                        <p>Alternatively, you can upload a photo file using the "Upload File" button.</p>
                                                    </div>
                                                </div>
                                                
                                                <!-- Modal Footer -->
                                                <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700">
                                                    <button type="button" @click="showPermissionModal = false" class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                        Close
                                                    </button>
                                                    <button type="button" @click="showPermissionModal = false; startCamera()" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-amber-600 hover:bg-amber-700">
                                                        <i class="fas fa-camera mr-2"></i>Try Again
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4">
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
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Address') }}</label>
                            <input type="text" name="address" x-model="form.address"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-amber-500 focus:ring-amber-500">
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
                                    <option value="male" @selected(old('gender') === 'male')>
                                        {{ __('staff_profiles.Male') }}</option>
                                    <option value="female" @selected(old('gender') === 'female')>
                                        {{ __('staff_profiles.Female') }}</option>
                                    <option value="other" @selected(old('gender') === 'other')>
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
                                    <option value="single" @selected(old('marital_status') === 'single')>
                                        {{ __('staff_profiles.Single') }}</option>
                                    <option value="married" @selected(old('marital_status') === 'married')>
                                        {{ __('staff_profiles.Married') }}</option>
                                    <option value="divorced" @selected(old('marital_status') === 'divorced')>
                                        {{ __('staff_profiles.Divorced') }}</option>
                                    <option value="widowed" @selected(old('marital_status') === 'widowed')>
                                        {{ __('staff_profiles.Widowed') }}</option>
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
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('staff_profiles.Portal Password') }}
                                    <span class="text-red-500">*</span></label>
                                <input type="password" name="password" required autocomplete="current-password"
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
                            {{ __('staff_profiles.Review details and submit to create the staff profile and portal access.') }}
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
                                {{ __('staff_profiles.Create Staff') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>