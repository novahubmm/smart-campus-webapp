<x-app-layout>
    <!-- Header Section -->
    <div class="bg-blue-600 pb-24 pt-6 px-6 rounded-b-[40px] relative overflow-hidden">
        <!-- Student Info Card -->
        <div class="bg-white/10 backdrop-blur-md rounded-3xl p-6 border border-white/20 relative z-10">
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <div class="w-16 h-16 rounded-full p-1 bg-gradient-to-tr from-blue-400 to-purple-500">
                        <img src="{{ $teacherProfile->photo_path ? asset('storage/' . $teacherProfile->photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($teacherProfile->user->name) . '&background=random' }}"
                            alt="Teacher Photo" class="w-full h-full rounded-full object-cover border-2 border-white">
                    </div>
                </div>

                <div class="text-white flex-1">
                    <h2 class="text-xl font-bold">{{ $teacherProfile->user->name }}</h2>
                    <div class="flex items-center text-blue-100 text-xs mt-1 space-x-2">
                        <span
                            class="bg-white/20 px-2 py-0.5 rounded">{{ $teacherProfile->position ?? 'Teacher' }}</span>
                        <span>•</span>
                        <span>{{ $teacherProfile->department->name ?? 'General Department' }}</span>
                    </div>
                    <div class="flex items-center text-yellow-300 text-xs mt-2 font-semibold">
                        <i class="fas fa-id-badge mr-1"></i> {{ $teacherProfile->employee_id ?? 'ID: —' }}
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-3 mt-6">
                <!-- Experience -->
                <div class="bg-blue-500 rounded-xl p-3 flex items-center space-x-3 shadow-sm">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div>
                        <p class="text-white font-bold text-sm">{{ $teacherProfile->previous_experience_years ?? 0 }}
                            Years</p>
                        <p class="text-blue-50 dark:text-blue-200 text-[10px]">{{ __('Experience') }}</p>
                    </div>
                </div>

                <!-- Qualification -->
                <div class="bg-purple-500 rounded-xl p-3 flex items-center space-x-3 shadow-sm">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div>
                        <p class="text-white font-bold text-sm">
                            {{ Str::limit($teacherProfile->qualification ?? 'N/A', 15) }}</p>
                        <p class="text-purple-50 dark:text-purple-200 text-[10px]">{{ __('Qualification') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="px-6 -mt-10 relative z-20 pb-24 space-y-6" x-data="{ activeTab: 'personal' }">

        <!-- Tabs -->
        <div
            class="bg-white dark:bg-gray-800 rounded-full p-1.5 flex shadow-sm border border-gray-100 dark:border-gray-700 overflow-x-auto">
            <button @click="activeTab = 'personal'"
                :class="activeTab === 'personal' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-500 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white'"
                class="flex-1 rounded-full py-2 text-xs font-bold px-4 whitespace-nowrap transition">
                {{ __('Personal Info') }}
            </button>
            <button @click="activeTab = 'professional'"
                :class="activeTab === 'professional' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-500 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white'"
                class="flex-1 rounded-full py-2 text-xs font-bold px-4 whitespace-nowrap transition">
                {{ __('Professional Info') }}
            </button>
        </div>

        <!-- Personal Info Content -->
        <div x-show="activeTab === 'personal'" class="space-y-6 animate-fadeIn">
            <div
                class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 space-y-4">
                <h3 class="font-bold text-gray-800 dark:text-white mb-4">{{ __('Personal Details') }}</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Date of Birth') }}</div>
                        <div class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $teacherProfile->dob ? $teacherProfile->dob->format('d M, Y') : '—' }}</div>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Phone / Mobile') }}</div>
                        <div class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $teacherProfile->phone_no ?? '—' }}</div>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Gender') }}</div>
                        <div class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ ucfirst($teacherProfile->gender) ?? '—' }}</div>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('NRC Number') }}</div>
                        <div class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $teacherProfile->nrc ?? '—' }}</div>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl col-span-1 md:col-span-2">
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Address') }}</div>
                        <div class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $teacherProfile->address ?? '—' }}</div>
                    </div>
                </div>

                <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                    <h4 class="font-semibold text-sm text-gray-800 dark:text-white mb-3">{{ __('Family Details') }}</h4>
                    <div class="grid grid-cols-1 gap-3">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('Father\'s Name') }}</span>
                            <span
                                class="font-medium text-gray-900 dark:text-white">{{ $teacherProfile->father_name ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('Mother\'s Name') }}</span>
                            <span
                                class="font-medium text-gray-900 dark:text-white">{{ $teacherProfile->mother_name ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('Marital Status') }}</span>
                            <span
                                class="font-medium text-gray-900 dark:text-white">{{ ucfirst($teacherProfile->marital_status) ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Professional Info Content -->
        <div x-show="activeTab === 'professional'" class="space-y-6 animate-fadeIn" style="display: none;">
            <div
                class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 space-y-4">
                <h3 class="font-bold text-gray-800 dark:text-white mb-4">{{ __('Professional Details') }}</h3>

                <div class="grid grid-cols-1 gap-4">
                    <div
                        class="p-4 bg-blue-50 dark:bg-blue-900/10 rounded-xl border border-blue-100 dark:border-blue-800/30">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ __('Join Date') }}</span>
                        </div>
                        <div class="text-xl font-bold text-blue-700 dark:text-blue-300 ml-11">
                            {{ $teacherProfile->hire_date ? $teacherProfile->hire_date->format('d M, Y') : '—' }}
                        </div>
                    </div>

                    <div
                        class="p-4 bg-green-50 dark:bg-green-900/10 rounded-xl border border-green-100 dark:border-green-800/30">
                        <div class="flex items-center gap-3 mb-2">
                            <div
                                class="w-8 h-8 rounded-lg bg-green-100 text-green-600 flex items-center justify-center">
                                <i class="fas fa-building"></i>
                            </div>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ __('Department') }}</span>
                        </div>
                        <div class="text-lg font-bold text-green-700 dark:text-green-300 ml-11">
                            {{ $teacherProfile->department->name ?? '—' }}
                        </div>
                    </div>

                    <div class="space-y-3 pt-2">
                        <h4 class="font-semibold text-sm text-gray-800 dark:text-white">{{ __('Emergency Contact') }}
                        </h4>
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl flex items-center justify-between">
                            <div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Contact Person') }}</div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $teacherProfile->emergency_contact ?? '—' }}</div>
                            </div>
                            <a href="tel:{{ $teacherProfile->emergency_contact }}"
                                class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center hover:bg-green-200 transition">
                                <i class="fas fa-phone"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>