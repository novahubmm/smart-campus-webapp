<x-app-layout>
    <div class="p-6 space-y-6" x-data="{ showForm: false }">
        <!-- Back Button & Header -->
        <div class="flex items-center justify-between">
            <a href="{{ route('guardian.students') }}" class="flex items-center text-gray-600 dark:text-gray-400">
                <i class="fas fa-chevron-left mr-2"></i>
                <span class="text-sm font-medium">{{ __('Back') }}</span>
            </a>
            <h1 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('Leave Request') }}</h1>
            <div class="w-8"></div> <!-- Spacer -->
        </div>

        @if(session('success'))
            <div
                class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-2xl text-xs font-bold animate-fadeIn">
                {{ session('success') }}
            </div>
        @endif

        @if($student)
            <!-- Leave Stats -->
            <div class="grid grid-cols-3 gap-3">
                <div
                    class="bg-white dark:bg-gray-800 p-3 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 text-center">
                    <span class="text-[9px] font-bold text-gray-400 uppercase block mb-1">{{ __('Used') }}</span>
                    <span class="text-lg font-black text-gray-800 dark:text-white">{{ $stats['total_days_taken'] }}</span>
                    <span class="text-[8px] text-gray-400 block">{{ __('Days') }}</span>
                </div>
                <div
                    class="bg-white dark:bg-gray-800 p-3 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 text-center">
                    <span class="text-[9px] font-bold text-gray-400 uppercase block mb-1">{{ __('Available') }}</span>
                    <span class="text-lg font-black text-blue-600 dark:text-blue-400">{{ $stats['remaining_days'] }}</span>
                    <span class="text-[8px] text-gray-400 block">{{ __('Days') }}</span>
                </div>
                <div
                    class="bg-white dark:bg-gray-800 p-3 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 text-center">
                    <span class="text-[9px] font-bold text-gray-400 uppercase block mb-1">{{ __('Pending') }}</span>
                    <span class="text-lg font-black text-yellow-600 dark:text-yellow-400">{{ $stats['pending'] }}</span>
                    <span class="text-[8px] text-gray-400 block">{{ __('Requests') }}</span>
                </div>
            </div>

            <!-- Action Button -->
            <button @click="showForm = !showForm"
                class="w-full py-4 bg-blue-600 text-white rounded-3xl font-bold text-sm shadow-lg shadow-blue-200 dark:shadow-none transition-all active:scale-95 flex items-center justify-center">
                <i class="fas fa-plus-circle mr-2" :class="showForm ? 'rotate-45' : ''"></i>
                <span x-text="showForm ? '{{ __('Cancel') }}' : '{{ __('New Leave Request') }}'"></span>
            </button>

            <!-- New Request Form -->
            <div x-show="showForm" x-collapse>
                <form action="{{ route('guardian.leave-requests.store') }}" method="POST"
                    class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 space-y-4">
                    @csrf
                    <div>
                        <label
                            class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">{{ __('Leave Type') }}</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($leaveTypes as $type)
                                <label class="relative">
                                    <input type="radio" name="leave_type" value="{{ $type['name'] }}" class="peer hidden"
                                        required>
                                    <div
                                        class="p-3 border border-gray-100 dark:border-gray-700 rounded-2xl text-center cursor-pointer peer-checked:bg-blue-50 peer-checked:border-blue-500 peer-checked:text-blue-600 dark:peer-checked:bg-blue-900/20 dark:peer-checked:text-blue-400 transition-all">
                                        <i class="fas fa-{{ $type['icon'] }} block mb-1"></i>
                                        <span class="text-[10px] font-bold">{{ $type['name'] }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">{{ __('Start Date') }}</label>
                            <input type="date" name="start_date"
                                class="w-full bg-gray-50 dark:bg-gray-900 border-none rounded-2xl text-sm focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>
                        <div>
                            <label
                                class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">{{ __('End Date') }}</label>
                            <input type="date" name="end_date"
                                class="w-full bg-gray-50 dark:bg-gray-900 border-none rounded-2xl text-sm focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">{{ __('Reason') }}</label>
                        <textarea name="reason" rows="3"
                            class="w-full bg-gray-50 dark:bg-gray-900 border-none rounded-2xl text-sm focus:ring-2 focus:ring-blue-500 placeholder-gray-400"
                            placeholder="{{ __('Please explain the reason for leave...') }}" required></textarea>
                    </div>

                    <button type="submit"
                        class="w-full py-4 bg-blue-600 text-white rounded-2xl font-bold text-sm hover:bg-blue-700 transition-colors">
                        {{ __('Submit Request') }}
                    </button>
                </form>
            </div>

            <!-- History -->
            <div class="space-y-4">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white flex items-center">
                    <i class="fas fa-history text-blue-500 mr-2"></i>
                    {{ __('Leave History') }}
                </h3>

                @if(count($requests) > 0)
                    <div class="space-y-3">
                        @foreach($requests as $request)
                            <div
                                class="bg-white dark:bg-gray-800 p-4 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex items-center">
                                        <div
                                            class="w-10 h-10 rounded-2xl bg-gray-50 dark:bg-gray-900 flex items-center justify-center mr-3">
                                            <i class="fas fa-{{ $request['leave_type']['icon'] }} text-blue-500"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-800 dark:text-white text-sm">
                                                {{ $request['leave_type']['name'] }}</h4>
                                            <p class="text-[10px] text-gray-400 font-medium">
                                                {{ Carbon\Carbon::parse($request['start_date'])->format('d M') }} -
                                                {{ Carbon\Carbon::parse($request['end_date'])->format('d M, Y') }}</p>
                                        </div>
                                    </div>
                                    <span class="text-[9px] px-2 py-0.5 rounded-full font-black uppercase {{ 
                                        $request['status'] == 'approved' ? 'bg-green-100 text-green-600' :
                            ($request['status'] == 'pending' ? 'bg-yellow-100 text-yellow-600' : 'bg-red-100 text-red-600') 
                                    }}">
                                        {{ __($request['status']) }}
                                    </span>
                                </div>
                                <p
                                    class="text-[11px] text-gray-600 dark:text-gray-400 leading-relaxed italic border-l-2 border-gray-100 dark:border-gray-700 pl-3">
                                    "{{ $request['reason'] }}"
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div
                        class="bg-gray-50 dark:bg-gray-800/50 rounded-3xl p-8 text-center border-2 border-dashed border-gray-100 dark:border-gray-700">
                        <p class="text-xs text-gray-400">{{ __('No leave history available.') }}</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>