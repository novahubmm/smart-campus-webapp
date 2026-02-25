<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-school"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('settings.School Information') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="contactModal()">
        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl px-4 py-3 flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                    <span class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</span>
                </div>
            @endif

            <!-- School Profile Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('settings.School Profile') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('settings.Update the school brand, contact, and address details.') }}</p>
                </div>

                <form method="POST" action="{{ route('settings.school-info.update') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- Form Fields -->
                    <div class="p-6 space-y-5">
                        @if(auth()->user()->hasRole('system_admin'))
                        <!-- Logo Upload Section - System Admin Only -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                            @php
                                $shortLogoUrl = null;
                                if (!empty($setting?->school_short_logo_path)) {
                                    $shortLogoUrl = str_starts_with($setting->school_short_logo_path, 'http')
                                        ? $setting->school_short_logo_path
                                        : storage_url($setting->school_short_logo_path);
                                }

                                $bannerLogoUrl = null;
                                if (!empty($setting?->school_logo_path)) {
                                    $bannerLogoUrl = str_starts_with($setting->school_logo_path, 'http')
                                        ? $setting->school_logo_path
                                        : storage_url($setting->school_logo_path);
                                }
                            @endphp
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('settings.Short Logo') }}</label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ __('settings.Used for favicon and navigation (recommended: square, 512x512px)') }}</p>
                                @if($shortLogoUrl)
                                    <div class="mb-2">
                                        <img src="{{ $shortLogoUrl }}" alt="Short Logo" class="h-16 w-16 object-contain border border-gray-300 dark:border-gray-600 rounded-lg p-2 bg-white dark:bg-gray-700">
                                    </div>
                                @endif
                                <input type="file" name="school_short_logo" accept="image/png,image/jpeg,image/jpg,image/svg+xml"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('school_short_logo')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('settings.Banner Logo') }}</label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ __('settings.Used for dashboard and headers (recommended: wide, 1200x400px)') }}</p>
                                @if($bannerLogoUrl)
                                    <div class="mb-2">
                                        <img src="{{ $bannerLogoUrl }}" alt="Banner Logo" class="h-16 w-auto object-contain border border-gray-300 dark:border-gray-600 rounded-lg p-2 bg-white dark:bg-gray-700">
                                    </div>
                                @endif
                                <input type="file" name="school_logo" accept="image/png,image/jpeg,image/jpg,image/svg+xml"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('school_logo')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.School Name') }}</label>
                                <input type="text" name="school_name" value="{{ old('school_name', $setting?->school_name) }}" required
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('school_name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.School Name (Myanmar)') }}</label>
                                <input type="text" name="school_name_mm" value="{{ old('school_name_mm', $setting?->school_name_mm) }}"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('school_name_mm')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.School Code') }}</label>
                                <input type="text" name="school_code" value="{{ old('school_code', $setting?->school_code) }}"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('school_code')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.Established Year') }}</label>
                                <input type="number" name="established_year" value="{{ old('established_year', $setting?->established_year) }}" min="1800" max="{{ date('Y') }}"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('established_year')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.Principal') }}</label>
                                <input type="text" name="principal_name" value="{{ old('principal_name', $setting?->principal_name) }}"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('principal_name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.Motto') }}</label>
                                <input type="text" name="motto" value="{{ old('motto', $setting?->motto) }}"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('motto')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.Motto (Myanmar)') }}</label>
                                <input type="text" name="motto_mm" value="{{ old('motto_mm', $setting?->motto_mm) }}"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('motto_mm')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.School Email') }}</label>
                                <input type="email" name="school_email" value="{{ old('school_email', $setting?->school_email) }}"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('school_email')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.School Phone') }}</label>
                                <input type="text" name="school_phone" value="{{ old('school_phone', $setting?->school_phone) }}"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('school_phone')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.Website') }}</label>
                                <input type="text" name="school_website" value="{{ old('school_website', $setting?->school_website) }}"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('school_website')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.School Address') }}</label>
                            <textarea name="school_address" rows="3"
                                      class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('school_address', $setting?->school_address) }}</textarea>
                            @error('school_address')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.About Us') }}</label>
                            <textarea name="school_about_us" rows="3"
                                      class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('school_about_us', $setting?->school_about_us) }}</textarea>
                            @error('school_about_us')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.About Us (Myanmar)') }}</label>
                            <textarea name="school_about_us_mm" rows="3"
                                      class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('school_about_us_mm', $setting?->school_about_us_mm) }}</textarea>
                            @error('school_about_us_mm')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.Vision') }}</label>
                                <textarea name="vision" rows="2"
                                          class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('vision', $setting?->vision) }}</textarea>
                                @error('vision')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.Vision (Myanmar)') }}</label>
                                <textarea name="vision_mm" rows="2"
                                          class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('vision_mm', $setting?->vision_mm) }}</textarea>
                                @error('vision_mm')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.Mission') }}</label>
                                <textarea name="mission" rows="2"
                                          class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('mission', $setting?->mission) }}</textarea>
                                @error('mission')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.Mission (Myanmar)') }}</label>
                                <textarea name="mission_mm" rows="2"
                                          class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('mission_mm', $setting?->mission_mm) }}</textarea>
                                @error('mission_mm')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.Pass Rate (%)') }}</label>
                                <input type="number" name="pass_rate" value="{{ old('pass_rate', $setting?->pass_rate) }}" min="0" max="100" step="0.1"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('pass_rate')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.Average Attendance (%)') }}</label>
                                <input type="number" name="average_attendance" value="{{ old('average_attendance', $setting?->average_attendance) }}" min="0" max="100" step="0.1"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('average_attendance')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-end gap-3">
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">
                            {{ __('settings.Cancel') }}
                        </a>
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm">
                            {{ __('settings.Save Changes') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Social Media Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-share-nodes text-blue-600"></i>
                        {{ __('settings.Social Media Links') }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('settings.Add your school social media profiles.') }}</p>
                </div>

                <form method="POST" action="{{ route('settings.school-info.update') }}">
                    @csrf

                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    <i class="fab fa-facebook text-blue-600 mr-1"></i>
                                    {{ __('settings.Facebook') }}
                                </label>
                                <input type="text" name="social_facebook" value="{{ old('social_facebook', $setting?->social_facebook) }}" placeholder="https://facebook.com/yourschool"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('social_facebook')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    <i class="fab fa-twitter text-sky-500 mr-1"></i>
                                    {{ __('settings.Twitter') }}
                                </label>
                                <input type="text" name="social_twitter" value="{{ old('social_twitter', $setting?->social_twitter) }}" placeholder="https://twitter.com/yourschool"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('social_twitter')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    <i class="fab fa-instagram text-pink-600 mr-1"></i>
                                    {{ __('settings.Instagram') }}
                                </label>
                                <input type="text" name="social_instagram" value="{{ old('social_instagram', $setting?->social_instagram) }}" placeholder="https://instagram.com/yourschool"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('social_instagram')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    <i class="fab fa-youtube text-red-600 mr-1"></i>
                                    {{ __('settings.YouTube') }}
                                </label>
                                <input type="text" name="social_youtube" value="{{ old('social_youtube', $setting?->social_youtube) }}" placeholder="https://youtube.com/yourschool"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('social_youtube')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                <i class="fab fa-linkedin text-blue-700 mr-1"></i>
                                {{ __('settings.LinkedIn') }}
                            </label>
                            <input type="text" name="social_linkedin" value="{{ old('social_linkedin', $setting?->social_linkedin) }}" placeholder="https://linkedin.com/company/yourschool"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('social_linkedin')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-end gap-3">
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm">
                            <i class="fas fa-save mr-2"></i>{{ __('settings.Save Social Media') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Working Hours Settings Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-clock text-blue-600"></i>
                        {{ __('settings.Working Hours Settings') }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('settings.Configure office hours and attendance policies for staff and teachers.') }}</p>
                </div>

                <form method="POST" action="{{ route('settings.working-hours.update') }}">
                    @csrf

                    <div class="p-6 space-y-6">
                        <!-- Working Days -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">{{ __('settings.Working Days') }}</label>
                            <div class="flex flex-wrap gap-3">
                                @php
                                    $days = [
                                        1 => 'Monday',
                                        2 => 'Tuesday',
                                        3 => 'Wednesday',
                                        4 => 'Thursday',
                                        5 => 'Friday',
                                        6 => 'Saturday',
                                        7 => 'Sunday'
                                    ];
                                    $selectedDays = old('office_working_days', $setting?->office_working_days ?? [1, 2, 3, 4, 5]);
                                @endphp
                                @foreach($days as $value => $label)
                                    <label class="flex items-center gap-2 px-4 py-2.5 rounded-lg border-2 cursor-pointer transition-all
                                        {{ in_array($value, $selectedDays) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-blue-300' }}">
                                        <input type="checkbox" name="office_working_days[]" value="{{ $value }}"
                                               {{ in_array($value, $selectedDays) ? 'checked' : '' }}
                                               class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.' . $label) }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('office_working_days')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Office Hours -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    <i class="fas fa-sign-in-alt text-green-600 mr-1"></i>
                                    {{ __('settings.Office Start Time') }}
                                </label>
                                <input type="time" name="office_start_time" value="{{ old('office_start_time', $setting?->office_start_time ?? '08:00') }}"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('office_start_time')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    <i class="fas fa-sign-out-alt text-red-600 mr-1"></i>
                                    {{ __('settings.Office End Time') }}
                                </label>
                                <input type="time" name="office_end_time" value="{{ old('office_end_time', $setting?->office_end_time ?? '17:00') }}"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('office_end_time')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    <i class="fas fa-coffee text-amber-600 mr-1"></i>
                                    {{ __('settings.Break Duration (minutes)') }}
                                </label>
                                <input type="number" name="office_break_duration_minutes" min="0" max="240" value="{{ old('office_break_duration_minutes', $setting?->office_break_duration_minutes ?? 60) }}"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('office_break_duration_minutes')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <!-- Required Working Hours -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    <i class="fas fa-hourglass-half text-purple-600 mr-1"></i>
                                    {{ __('settings.Required Working Hours') }}
                                </label>
                                <input type="number" name="required_working_hours" min="0" max="24" step="0.5" value="{{ old('required_working_hours', $setting?->required_working_hours ?? 8.00) }}"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('settings.Total hours required per day (e.g., 8.0 or 7.5)') }}</p>
                                @error('required_working_hours')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    <i class="fas fa-user-clock text-indigo-600 mr-1"></i>
                                    {{ __('settings.Late Arrival Grace Period (minutes)') }}
                                </label>
                                <input type="number" name="late_arrival_grace_minutes" min="0" max="60" value="{{ old('late_arrival_grace_minutes', $setting?->late_arrival_grace_minutes ?? 15) }}"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('settings.Grace period before marking as late') }}</p>
                                @error('late_arrival_grace_minutes')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <!-- Policies -->
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors">
                                <input type="checkbox" name="allow_early_checkout" value="1"
                                       {{ old('allow_early_checkout', $setting?->allow_early_checkout ?? true) ? 'checked' : '' }}
                                       class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                <div class="flex-1">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                        <i class="fas fa-door-open text-blue-600"></i>
                                        {{ __('settings.Allow Early Checkout') }}
                                    </span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ __('settings.Staff can check out before completing required hours (with confirmation)') }}</p>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors">
                                <input type="checkbox" name="track_overtime" value="1"
                                       {{ old('track_overtime', $setting?->track_overtime ?? true) ? 'checked' : '' }}
                                       class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                <div class="flex-1">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                        <i class="fas fa-chart-line text-green-600"></i>
                                        {{ __('settings.Track Overtime Hours') }}
                                    </span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ __('settings.Record hours worked beyond required working hours') }}</p>
                                </div>
                            </label>
                        </div>

                        <!-- Info Box -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <div class="flex gap-3">
                                <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mt-0.5"></i>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-1">{{ __('settings.How it works') }}</h4>
                                    <ul class="text-xs text-blue-800 dark:text-blue-200 space-y-1">
                                        <li>• {{ __('settings.These settings apply to both teachers and staff attendance tracking') }}</li>
                                        <li>• {{ __('settings.Working hours are calculated excluding break duration') }}</li>
                                        <li>• {{ __('settings.Mobile app will use these settings for check-in/check-out validation') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-end gap-3">
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm">
                            <i class="fas fa-save mr-2"></i>{{ __('settings.Save Working Hours Settings') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Key Contacts Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('settings.Key Contacts') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('settings.Keep important school contacts up to date.') }}</p>
                    </div>
                    <button type="button" @click="openCreate()" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                        <i class="fas fa-plus"></i>{{ __('settings.Add Contact') }}
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('settings.Name') }}</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('settings.Role') }}</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('settings.Email') }}</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('settings.Phone') }}</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('settings.Status') }}</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('settings.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($contacts as $contact)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-5 py-4">
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ $contact->name }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-gray-600 dark:text-gray-300">{{ $contact->role ?? '—' }}</td>
                                    <td class="px-5 py-4 text-gray-600 dark:text-gray-300">{{ $contact->email ?? '—' }}</td>
                                    <td class="px-5 py-4 text-gray-600 dark:text-gray-300">{{ $contact->phone ?? '—' }}</td>
                                    <td class="px-5 py-4">
                                        @if($contact->is_primary)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">{{ __('settings.Primary') }}</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">{{ __('settings.Secondary') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button"
                                                    @click="openEdit({
                                                        id: '{{ $contact->id }}',
                                                        name: '{{ addslashes($contact->name) }}',
                                                        role: '{{ addslashes($contact->role) }}',
                                                        email: '{{ addslashes($contact->email) }}',
                                                        phone: '{{ addslashes($contact->phone) }}',
                                                        is_primary: {{ $contact->is_primary ? 'true' : 'false' }}
                                                    })"
                                                    class="w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 flex items-center justify-center" title="{{ __('settings.Edit') }}">
                                                <i class="fas fa-pen-to-square text-sm"></i>
                                            </button>
                                            <form method="POST" action="{{ route('settings.key-contacts.destroy', $contact) }}" class="inline"
                                                  @submit.prevent="$dispatch('confirm-show', {
                                                      title: '{{ __('settings.Delete contact') }}',
                                                      message: '{{ __('settings.Are you sure you want to remove this contact?') }}',
                                                      confirmText: '{{ __('settings.Delete') }}',
                                                      cancelText: '{{ __('settings.Cancel') }}',
                                                      onConfirm: () => $el.submit()
                                                  })">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 flex items-center justify-center" title="{{ __('settings.Delete') }}">
                                                    <i class="fas fa-trash text-sm"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">{{ __('settings.No contacts added yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Contact Modal -->
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="modalOpen = false">
            <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-lg shadow-2xl" @click.stop>
                <form :action="formAction" method="POST">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide" x-text="isEdit ? '{{ __('settings.Edit Contact') }}' : '{{ __('settings.New Contact') }}'"></p>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white" x-text="isEdit ? '{{ __('settings.Update Contact') }}' : '{{ __('settings.Add Contact') }}'"></h3>
                        </div>
                        <button type="button" class="w-9 h-9 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700" @click="modalOpen = false">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.Name') }}</label>
                            <input type="text" name="name" x-model="form.name" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.Role') }}</label>
                            <input type="text" name="role" x-model="form.role" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.Email') }}</label>
                                <input type="email" name="email" x-model="form.email" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.Phone') }}</label>
                                <input type="text" name="phone" x-model="form.phone" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" name="is_primary" value="1" x-model="form.is_primary" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
                            <span>{{ __('settings.Primary contact') }}</span>
                        </label>
                    </div>
                    <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 rounded-b-xl">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">
                            {{ __('settings.Cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm">
                            {{ __('settings.Save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function contactModal() {
            return {
                modalOpen: false,
                formAction: '{{ route('settings.key-contacts.store') }}',
                isEdit: false,
                contactId: null,
                form: { name: '', role: '', email: '', phone: '', is_primary: false },
                openCreate() {
                    this.isEdit = false;
                    this.formAction = '{{ route('settings.key-contacts.store') }}';
                    this.form = { name: '', role: '', email: '', phone: '', is_primary: false };
                    this.modalOpen = true;
                },
                openEdit(contact) {
                    this.isEdit = true;
                    this.contactId = contact.id;
                    this.formAction = '{{ url('/settings/key-contacts') }}/' + contact.id;
                    this.form = {
                        name: contact.name || '',
                        role: contact.role || '',
                        email: contact.email || '',
                        phone: contact.phone || '',
                        is_primary: contact.is_primary || false,
                    };
                    this.modalOpen = true;
                }
            }
        }
    </script>
</x-app-layout>
