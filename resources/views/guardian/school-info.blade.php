<x-app-layout>
    <div class="p-6 space-y-6">
        <!-- Back Button & Header -->
        <div class="flex items-center justify-between">
            <a href="{{ route('guardian.utilities') }}" class="flex items-center text-gray-600 dark:text-gray-300">
                <i class="fas fa-chevron-left mr-2"></i>
                <span class="text-sm font-medium">{{ __('Back') }}</span>
            </a>
            <h1 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('School Info') }}</h1>
            <div class="w-8"></div> <!-- Spacer -->
        </div>

        <div class="space-y-6">
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <!-- School Hero -->
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                    <div class="flex flex-col md:flex-row gap-6 items-start md:items-center">
                        <div
                            class="w-20 h-20 rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-school text-3xl text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $setting->school_name ?? __('N/A') }}
                            </h1>
                            <p class="text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-2">
                                <i class="fas fa-map-marker-alt text-red-500"></i>
                                {{ $setting->school_address ?? __('N/A') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="grid grid-cols-1 lg:grid-cols-3 divide-y lg:divide-y-0 lg:divide-x divide-gray-100 dark:divide-gray-700">
                    <!-- About Us -->
                    <div class="p-6 lg:col-span-2">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <i class="fas fa-info-circle text-blue-500"></i>
                            {{ __('About Our School') }}
                        </h3>
                        <div
                            class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed">
                            {!! nl2br(e($setting->school_about_us ?? __('No information available.'))) !!}
                        </div>

                        @if($setting->principal_name)
                            <div
                                class="mt-8 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-100 dark:border-gray-700 flex items-center gap-4">
                                <div
                                    class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                    <i class="fas fa-user-tie text-xl"></i>
                                </div>
                                <div>
                                    <p
                                        class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">
                                        {{ __('Principal') }}
                                    </p>
                                    <p class="text-base font-bold text-gray-900 dark:text-white">
                                        {{ $setting->principal_name }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Contact Details -->
                    <div class="p-6 bg-gray-50/30 dark:bg-gray-800/30">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <i class="fas fa-address-book text-blue-500"></i>
                            {{ __('Contact Details') }}
                        </h3>

                        <div class="space-y-4">
                            @if($setting->school_phone)
                                <div class="flex items-start gap-4">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-phone text-green-600 dark:text-green-400"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Phone Number') }}</p>
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ $setting->school_phone }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            @if($setting->school_email)
                                <div class="flex items-start gap-4">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-envelope text-orange-600 dark:text-orange-400"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Email Address') }}</p>
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ $setting->school_email }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            @if($setting->school_website)
                                <div class="flex items-start gap-4">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-globe text-purple-600 dark:text-purple-400"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Website') }}</p>
                                        <a href="{{ $setting->school_website }}" target="_blank"
                                            class="font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                            {{ str_replace(['http://', 'https://'], '', $setting->school_website) }}
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if($contacts->count() > 0)
                            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4 uppercase tracking-wider">
                                    {{ __('Key Contacts') }}
                                </h4>
                                <div class="space-y-3">
                                    @foreach($contacts as $contact)
                                        <div
                                            class="flex items-center gap-3 p-2 rounded-lg hover:bg-white dark:hover:bg-gray-700 transition-colors">
                                            <div
                                                class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center text-xs font-bold text-blue-600 dark:text-blue-400">
                                                {{ substr($contact->name, 0, 1) }}
                                            </div>
                                            <div class="flex-grow overflow-hidden">
                                                <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
                                                    {{ $contact->name }}
                                                </p>
                                                <p
                                                    class="text-[10px] text-gray-500 dark:text-gray-400 font-medium truncate uppercase tracking-tighter">
                                                    {{ $contact->role }}
                                                </p>
                                            </div>
                                            @if($contact->phone)
                                                <a href="tel:{{ $contact->phone }}"
                                                    class="w-7 h-7 flex-shrink-0 flex items-center justify-center text-gray-400 hover:text-blue-500">
                                                    <i class="fas fa-phone-alt text-xs"></i>
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
</x-app-layout>