<x-app-layout>
    <div class="p-6 space-y-6">
        <!-- Back Button & Header -->
        <div class="flex items-center justify-between">
            <a href="{{ route('guardian.students') }}" class="flex items-center text-gray-600 dark:text-gray-400">
                <i class="fas fa-chevron-left mr-2"></i>
                <span class="text-sm font-medium">{{ __('Back') }}</span>
            </a>
            <h1 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('View Homework') }}</h1>
            <div class="w-8"></div> <!-- Spacer -->
        </div>

        @if($student)
            <!-- Homework Summary -->
            <div class="grid grid-cols-3 gap-3">
                <div
                    class="bg-blue-50 dark:bg-blue-900/10 p-3 rounded-2xl border border-blue-100 dark:border-blue-800/20 text-center">
                    <span
                        class="text-[9px] font-bold text-blue-600 dark:text-blue-400 uppercase block mb-1">{{ __('Total') }}</span>
                    <span class="text-lg font-black text-gray-800 dark:text-white">{{ $stats['total'] }}</span>
                </div>
                <div
                    class="bg-orange-50 dark:bg-orange-900/10 p-3 rounded-2xl border border-orange-100 dark:border-orange-800/20 text-center">
                    <span
                        class="text-[9px] font-bold text-orange-600 dark:text-orange-400 uppercase block mb-1">{{ __('Pending') }}</span>
                    <span class="text-lg font-black text-gray-800 dark:text-white">{{ $stats['pending'] }}</span>
                </div>
                <div
                    class="bg-green-50 dark:bg-green-900/10 p-3 rounded-2xl border border-green-100 dark:border-green-800/20 text-center">
                    <span
                        class="text-[9px] font-bold text-green-600 dark:text-green-400 uppercase block mb-1">{{ __('Done') }}</span>
                    <span class="text-lg font-black text-gray-800 dark:text-white">{{ $stats['completed'] }}</span>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="flex space-x-2 border-b border-gray-100 dark:border-gray-700">
                @foreach(['all', 'pending', 'completed', 'overdue'] as $tab)
                    <a href="{{ route('guardian.homework', ['status' => $tab == 'all' ? null : $tab]) }}"
                        class="pb-3 px-2 text-xs font-bold uppercase tracking-wider transition-all {{ ($status ?? 'all') == $tab ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-400 border-transparent' }}">
                        {{ __($tab) }}
                    </a>
                @endforeach
            </div>

            <!-- Homework List -->
            @if(count($homework) > 0)
                <div class="space-y-4">
                    @foreach($homework as $item)
                        <div
                            class="bg-white dark:bg-gray-800 rounded-3xl p-5 shadow-sm border border-gray-50 dark:border-gray-700 transition-all active:scale-[0.98]">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-gray-50 dark:bg-gray-900 flex items-center justify-center mr-4">
                                        <i class="fas fa-{{ $item['subject_icon'] ?? 'book' }} text-blue-500 text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-800 dark:text-white text-base leading-tight">
                                            {{ $item['title'] }}</h4>
                                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-tight mt-1">
                                            {{ $item['subject'] }}</p>
                                    </div>
                                </div>
                                @php
                                    $priorityColor = [
                                        'high' => 'bg-red-100 text-red-600',
                                        'medium' => 'bg-orange-100 text-orange-600',
                                        'normal' => 'bg-blue-100 text-blue-600',
                                        'overdue' => 'bg-red-500 text-white',
                                    ];
                                @endphp
                                <span
                                    class="text-[8px] px-2 py-1 rounded-lg font-black uppercase tracking-widest {{ $priorityColor[$item['priority']] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ __($item['priority']) }}
                                </span>
                            </div>

                            <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2 mb-4">
                                {{ $item['description'] }}
                            </p>

                            <div class="flex items-center justify-between pt-4 border-t border-gray-50 dark:border-gray-700/50">
                                <div class="flex flex-col">
                                    <span
                                        class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter">{{ __('Due Date') }}</span>
                                    <div class="flex items-center text-xs font-bold text-gray-700 dark:text-gray-300">
                                        <i class="far fa-calendar-alt mr-1.5 text-blue-400"></i>
                                        {{ Carbon\Carbon::parse($item['due_date'])->format('d M, Y') }}
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    @if($item['status'] == 'completed')
                                        <div
                                            class="bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 p-2 rounded-xl">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                    @else
                                        <button
                                            class="bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider">
                                            {{ __('Detail') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div
                    class="bg-gray-50 dark:bg-gray-800/50 rounded-3xl p-12 text-center border-2 border-dashed border-gray-100 dark:border-gray-700">
                    <i class="fas fa-tasks text-3xl text-gray-300 dark:text-gray-600 mb-3"></i>
                    <h3 class="font-bold text-gray-800 dark:text-white">{{ __('No Homework') }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('No homework assignments found for this status.') }}</p>
                </div>
            @endif
        @endif
    </div>
</x-app-layout>