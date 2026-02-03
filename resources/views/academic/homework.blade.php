<x-app-layout>
    <x-slot name="header">
        <x-page-header 
            icon="fas fa-file-alt"
            iconBg="bg-blue-50 dark:bg-blue-900/30"
            iconColor="text-blue-700 dark:text-blue-200"
            :subtitle="__('Academic')"
            :title="__('homework.Homework Management')"
        />
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            {{-- Stats Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <i class="fas fa-file-alt text-blue-600 dark:text-blue-400 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalHomework }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('homework.Total Homework') }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                            <i class="fas fa-clock text-green-600 dark:text-green-400 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $activeHomework }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('homework.Active') }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                            <i class="fas fa-check-circle text-purple-600 dark:text-purple-400 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $completedHomework }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('homework.Completed') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Homework List --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('homework.Homework List') }}</h2>
                    <button onclick="openAddModal()" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 flex items-center gap-2 text-sm">
                        <i class="fas fa-plus"></i>
                        {{ __('homework.Add Homework') }}
                    </button>
                </div>

                {{-- Filters --}}
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                    <form method="GET" class="flex flex-wrap items-center gap-4">
                        <div class="flex-1 min-w-[150px]">
                            <select name="grade_id" id="filter-grade" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                <option value="">{{ __('homework.All Grades') }}</option>
                                @foreach($grades as $grade)
                                    <option value="{{ $grade->id }}" {{ $gradeId == $grade->id ? 'selected' : '' }}>@gradeName($grade->level)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1 min-w-[150px]">
                            <select name="class_id" id="filter-class" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                <option value="">{{ __('homework.All Classes') }}</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>@className($class->name, $class->grade?->level)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1 min-w-[150px]">
                            <select name="subject_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                <option value="">{{ __('homework.All Subjects') }}</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ $subjectId == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1 min-w-[150px]">
                            <select name="status" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                <option value="">{{ __('homework.All Status') }}</option>
                                <option value="active" {{ $status == 'active' ? 'selected' : '' }}>{{ __('homework.Active') }}</option>
                                <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>{{ __('homework.Completed') }}</option>
                                <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>{{ __('homework.Cancelled') }}</option>
                            </select>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 text-sm">
                            {{ __('homework.Apply') }}
                        </button>
                        <a href="{{ route('homework.index') }}" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 text-sm">
                            {{ __('homework.Reset') }}
                        </a>
                    </form>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('homework.Title') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('homework.Class') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('homework.Teacher') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('homework.Due Date') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('homework.Progress') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('homework.Status') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('homework.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($homework as $hw)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3">
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ $hw->title }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $hw->subject->name ?? '-' }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">@className($hw->schoolClass->name ?? '-', $hw->schoolClass?->grade?->level)</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $hw->teacher->user->name ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm {{ $hw->isOverdue() ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-700 dark:text-gray-300' }}">
                                            {{ $hw->due_date->format('M d, Y') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-16 bg-gray-200 dark:bg-gray-600 rounded-full h-1.5">
                                                @php
                                                    $totalStudents = $hw->schoolClass->enrolledStudents()->count();
                                                    $submitted = $hw->submission_count;
                                                    $percent = $totalStudents > 0 ? round(($submitted / $totalStudents) * 100) : 0;
                                                @endphp
                                                <div class="bg-primary-500 h-1.5 rounded-full" style="width: {{ $percent }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $submitted }}/{{ $totalStudents }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $statusColors = [
                                                'active' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                                                'completed' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
                                                'cancelled' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$hw->status] ?? '' }}">
                                            {{ __('homework.' . ucfirst($hw->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('homework.show', $hw->id) }}" class="p-1.5 text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button onclick="editHomework('{{ $hw->id }}')" class="p-1.5 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('homework.destroy', $hw->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('homework.Are you sure?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        {{ __('homework.No homework found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($homework->hasPages())
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $homework->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Add Homework Modal --}}
    <div id="add-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50" onclick="closeAddModal()"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('homework.Add Homework') }}</h3>
                    <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="{{ route('homework.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('homework.Title') }} *</label>
                            <input type="text" name="title" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('homework.Description') }}</label>
                            <textarea name="description" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('homework.Class') }} *</label>
                                <select name="class_id" id="modal-class" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="">{{ __('homework.Select Class') }}</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">@className($class->name, $class->grade?->level)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('homework.Subject') }} *</label>
                                <select name="subject_id" id="modal-subject" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="">{{ __('homework.Select Subject') }}</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('homework.Due Date') }} *</label>
                                <input type="date" name="due_date" required min="{{ date('Y-m-d') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('homework.Priority') }}</label>
                                <select name="priority" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="low">{{ __('homework.Low') }}</option>
                                    <option value="medium" selected>{{ __('homework.Medium') }}</option>
                                    <option value="high">{{ __('homework.High') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="closeAddModal()" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                            {{ __('homework.Cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                            {{ __('homework.Save Homework') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function openAddModal() {
        document.getElementById('add-modal').classList.remove('hidden');
    }

    function closeAddModal() {
        document.getElementById('add-modal').classList.add('hidden');
    }

    function editHomework(id) {
        window.location.href = `/homework/${id}`;
    }
    </script>
    @endpush
</x-app-layout>
