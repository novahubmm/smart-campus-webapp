<x-app-layout>
    <div class="p-6 space-y-6">
        <!-- Back Button & Header -->
        <div class="flex items-center justify-between">
            <a href="{{ route('guardian.students') }}" class="flex items-center text-gray-600 dark:text-gray-400">
                <i class="fas fa-chevron-left mr-2"></i>
                <span class="text-sm font-medium">{{ __('Back') }}</span>
            </a>
            <h1 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('attendance.Attendance') }}</h1>
            <div class="w-8"></div> <!-- Spacer -->
        </div>

        @if($student)
            <!-- Attendance Stats Overview -->
            <div class="grid grid-cols-2 gap-4">
                <div
                    class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-3xl p-5 text-white shadow-lg overflow-hidden relative">
                    <span
                        class="text-blue-100 text-[9px] font-bold uppercase tracking-wider block mb-1">{{ __('This Month') }}</span>
                    <h3 class="text-3xl font-black">{{ $stats['current_month_percentage'] }}%</h3>
                    <p class="text-[10px] text-blue-100 mt-2 flex items-center">
                        <i class="fas fa-chart-line mr-1"></i>
                        {{ __('Attendance Rate') }}
                    </p>
                    <i class="fas fa-calendar-check absolute -bottom-2 -right-2 text-5xl text-white/10"></i>
                </div>
                <div
                    class="bg-white dark:bg-gray-800 rounded-3xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <span
                        class="text-gray-400 text-[9px] font-bold uppercase tracking-wider block mb-1">{{ __('Year to Date') }}</span>
                    <h3 class="text-3xl font-black text-gray-800 dark:text-white">{{ $stats['year_to_date_percentage'] }}%
                    </h3>
                    <div class="mt-2 flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="text-[8px] text-green-500 font-bold uppercase">{{ __('Present') }}</span>
                            <span
                                class="text-sm font-bold text-gray-800 dark:text-white">{{ $stats['total_present'] }}</span>
                        </div>
                        <div class="flex flex-col text-right">
                            <span class="text-[8px] text-red-500 font-bold uppercase">{{ __('Absent') }}</span>
                            <span
                                class="text-sm font-bold text-gray-800 dark:text-white">{{ $stats['total_absent'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Filter -->
            <div
                class="bg-white dark:bg-gray-800 rounded-3xl p-3 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                @php
                    $currentDate = Carbon\Carbon::create($year, $month, 1);
                    $prevMonth = $currentDate->copy()->subMonth();
                    $nextMonth = $currentDate->copy()->addMonth();
                @endphp
                <a href="{{ route('guardian.attendance', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}"
                    class="w-10 h-10 rounded-2xl bg-gray-50 dark:bg-gray-700 flex items-center justify-center text-gray-500">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <div class="text-center">
                    <h4 class="text-sm font-bold text-gray-800 dark:text-white">{{ $currentDate->format('F Y') }}</h4>
                    <p class="text-[9px] text-gray-400 font-medium uppercase tracking-tighter">{{ __('Monthly Records') }}
                    </p>
                </div>
                <a href="{{ route('guardian.attendance', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}"
                    class="w-10 h-10 rounded-2xl bg-gray-50 dark:bg-gray-700 flex items-center justify-center text-gray-500">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>

            <!-- Attendance Records -->
            <div class="space-y-4">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white flex items-center">
                    <i class="fas fa-clipboard-list text-blue-500 mr-2"></i>
                    {{ __('Daily Log') }}
                </h3>

                @if(count($records) > 0)
                    <div class="space-y-3">
                        @foreach($records as $record)
                            <div
                                class="bg-white dark:bg-gray-800 p-4 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                <div class="flex items-center">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-gray-50 dark:bg-gray-900 flex flex-col items-center justify-center mr-4 border border-gray-100 dark:border-gray-700">
                                        <span
                                            class="text-[10px] font-black text-gray-400 uppercase leading-none">{{ Carbon\Carbon::parse($record['date'])->format('M') }}</span>
                                        <span
                                            class="text-lg font-black text-gray-800 dark:text-white leading-none mt-1">{{ Carbon\Carbon::parse($record['date'])->format('d') }}</span>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-800 dark:text-white text-sm capitalize">
                                            {{ $record['status'] }}</h4>
                                        <p class="text-[10px] text-gray-400 font-medium">
                                            @if($record['check_in_time'])
                                                <i class="far fa-clock mr-1"></i> In:
                                                {{ Carbon\Carbon::parse($record['check_in_time'])->format('h:i A') }}
                                            @else
                                                {{ __('No Timing Recorded') }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-[9px] px-3 py-1 rounded-full font-black uppercase {{ 
                                        $record['status'] == 'present' ? 'bg-green-100 text-green-600' :
                            ($record['status'] == 'late' ? 'bg-yellow-100 text-yellow-600' :
                                ($record['status'] == 'absent' ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600')) 
                                    }}">
                                        {{ __($record['status']) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div
                        class="bg-gray-50 dark:bg-gray-800/50 rounded-3xl p-12 text-center border-2 border-dashed border-gray-100 dark:border-gray-700">
                        <i class="fas fa-calendar-times text-3xl text-gray-300 dark:text-gray-600 mb-3"></i>
                        <h3 class="font-bold text-gray-800 dark:text-white">{{ __('No Records') }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('No attendance records for this month.') }}
                        </p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>