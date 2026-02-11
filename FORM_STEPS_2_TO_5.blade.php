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
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.DOB') }} <span class="text-red-500">*</span></label>
                                            <input type="date" name="dob" x-model="form.dob" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                                   :class="errors.dob ? 'field-error' : ''">
                                            <p x-show="errors.dob" x-text="errors.dob" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                            @error('dob')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Previous Grade') }} <span class="text-red-500">*</span></label>
                                            <input type="text" name="previous_grade" x-model="form.previous_grade" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                                   :class="errors.previous_grade ? 'field-error' : ''">
                                            <p x-show="errors.previous_grade" x-text="errors.previous_grade" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                            @error('previous_grade')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Previous Class') }} <span class="text-red-500">*</span></label>
                                            <input type="text" name="previous_class" x-model="form.previous_class" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                                   :class="errors.previous_class ? 'field-error' : ''">
                                            <p x-show="errors.previous_class" x-text="errors.previous_class" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                            @error('previous_class')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                        </div>
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
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Address') }}</label>
                                            <input type="text" name="address" x-model="form.address" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div></div>
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
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Previous School Name') }} <span class="text-red-500">*</span></label>
                                            <input type="text" name="previous_school_name" x-model="form.previous_school_name" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                                   :class="errors.previous_school_name ? 'field-error' : ''">
                                            <p x-show="errors.previous_school_name" x-text="errors.previous_school_name" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                            @error('previous_school_name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Previous School Address') }} <span class="text-red-500">*</span></label>
                                            <input type="text" name="previous_school_address" x-model="form.previous_school_address" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                                   :class="errors.previous_school_address ? 'field-error' : ''">
                                            <p x-show="errors.previous_school_address" x-text="errors.previous_school_address" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                            @error('previous_school_address')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
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
                                            <input type="text" name="father_name" x-model="form.father_name" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                                   :class="errors.father_name ? 'field-error' : ''">
                                            <p x-show="errors.father_name" x-text="errors.father_name" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                            @error('father_name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Father NRC') }} <span class="text-red-500">*</span></label>
                                            <input type="text" name="father_nrc" x-model="form.father_nrc" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                                   :class="errors.father_nrc ? 'field-error' : ''">
                                            <p x-show="errors.father_nrc" x-text="errors.father_nrc" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                            @error('father_nrc')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Father Religious') }} <span class="text-red-500">*</span></label>
                                            <input type="text" name="father_religious" x-model="form.father_religious" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                                   :class="errors.father_religious ? 'field-error' : ''">
                                            <p x-show="errors.father_religious" x-text="errors.father_religious" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                            @error('father_religious')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Father Phone No.') }}</label>
                                            <input type="text" name="father_phone_no" x-model="form.father_phone_no" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Father Occupation') }} <span class="text-red-500">*</span></label>
                                            <input type="text" name="father_occupation" x-model="form.father_occupation" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                                   :class="errors.father_occupation ? 'field-error' : ''">
                                            <p x-show="errors.father_occupation" x-text="errors.father_occupation" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                            @error('father_occupation')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Father Address') }} <span class="text-red-500">*</span></label>
                                            <input type="text" name="father_address" x-model="form.father_address" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                                   :class="errors.father_address ? 'field-error' : ''">
                                            <p x-show="errors.father_address" x-text="errors.father_address" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                            @error('father_address')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Mother Name') }} <span class="text-red-500">*</span></label>
                                            <input type="text" name="mother_name" x-model="form.mother_name" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                                   :class="errors.mother_name ? 'field-error' : ''">
                                            <p x-show="errors.mother_name" x-text="errors.mother_name" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                            @error('mother_name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Mother NRC') }} <span class="text-red-500">*</span></label>
                                            <input type="text" name="mother_nrc" x-model="form.mother_nrc" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                                   :class="errors.mother_nrc ? 'field-error' : ''">
                                            <p x-show="errors.mother_nrc" x-text="errors.mother_nrc" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                            @error('mother_nrc')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Mother Religious') }} <span class="text-red-500">*</span></label>
                                            <input type="text" name="mother_religious" x-model="form.mother_religious" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                                   :class="errors.mother_religious ? 'field-error' : ''">
                                            <p x-show="errors.mother_religious" x-text="errors.mother_religious" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                            @error('mother_religious')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Mother Phone No.') }}</label>
                                            <input type="text" name="mother_phone_no" x-model="form.mother_phone_no" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Mother Occupation') }} <span class="text-red-500">*</span></label>
                                            <input type="text" name="mother_occupation" x-model="form.mother_occupation" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                                   :class="errors.mother_occupation ? 'field-error' : ''">
                                            <p x-show="errors.mother_occupation" x-text="errors.mother_occupation" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                            @error('mother_occupation')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Mother Address') }} <span class="text-red-500">*</span></label>
                                            <input type="text" name="mother_address" x-model="form.mother_address" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                                   :class="errors.mother_address ? 'field-error' : ''">
                                            <p x-show="errors.mother_address" x-text="errors.mother_address" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                            @error('mother_address')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('student_profiles.Emergency Contact Phone No.') }}</label>
                                            <input type="text" name="emergency_contact_phone_no" x-model="form.emergency_contact_phone_no" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div></div>
                                    </div>
                                </div>
