    <!-- Select2 CSS -->
    <link href="/css/select2.min.css" rel="stylesheet" />
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-magic"></i>
            </div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('Academic Setup') }}</h1>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 overflow-x-hidden">
            <!-- Welcome Header -->
            <div class="mb-8 rounded-2xl p-8 shadow-lg bg-gradient-to-br from-blue-500 to-blue-700 dark:from-blue-600 dark:to-blue-800">
                <div class="flex flex-col sm:flex-row items-center gap-6">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center text-3xl text-white flex-shrink-0">
                        <i class="fas fa-magic"></i>
                    </div>
                    <div class="flex-1 text-center sm:text-left">
                        <h2 class="text-2xl sm:text-3xl font-bold mb-2 text-white">{{ __('Welcome to Academic Setup') }}</h2>
                        <p class="text-base sm:text-lg text-white/90">{{ __("Let's set up your academic structure step by step. This wizard will guide you through creating batches, grades, classes, rooms, and subjects.") }}</p>
                    </div>
                </div>
            </div>

            <!-- Wizard Progress Bar -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-5">
                <div class="wizard-progress-bar mb-8">
                    <div class="flex justify-between items-center">
                        <div class="wizard-step-item flex flex-col items-center cursor-pointer" data-step="1" data-active-bg="bg-blue-600" data-active-label="text-blue-900 dark:text-blue-200">
                            <div class="step-indicator rounded-full w-10 h-10 flex items-center justify-center mb-2 bg-blue-600 text-white">
                                <i class="fas fa-pencil-alt"></i>
                            </div>
                            <span class="step-label text-sm text-blue-900 dark:text-blue-200 font-semibold">{{ __('Batch') }}</span>
                        </div>
                        <div class="wizard-step-item flex flex-col items-center cursor-pointer" data-step="2" data-active-bg="bg-indigo-600" data-active-label="text-indigo-900 dark:text-indigo-200">
                            <div class="step-indicator rounded-full w-10 h-10 flex items-center justify-center mb-2 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <span class="step-label text-sm text-gray-600 dark:text-gray-400">{{ __('Grades & Classes') }}</span>
                        </div>
                        <div class="wizard-step-item flex flex-col items-center cursor-pointer" data-step="3" data-active-bg="bg-purple-600" data-active-label="text-purple-900 dark:text-purple-200">
                            <div class="step-indicator rounded-full w-10 h-10 flex items-center justify-center mb-2 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                <i class="fas fa-door-open"></i>
                            </div>
                            <span class="step-label text-sm text-gray-600 dark:text-gray-400">{{ __('Rooms') }}</span>
                        </div>
                        <div class="wizard-step-item flex flex-col items-center cursor-pointer" data-step="4" data-active-bg="bg-emerald-600" data-active-label="text-emerald-900 dark:text-emerald-200">
                            <div class="step-indicator rounded-full w-10 h-10 flex items-center justify-center mb-2 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                <i class="fas fa-book"></i>
                            </div>
                            <span class="step-label text-sm text-gray-600 dark:text-gray-400">{{ __('Subjects') }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col lg:flex-row gap-6">
                    <div class="flex-1">
                        <form id="wizardForm" method="POST" action="{{ route('academic-setup.complete') }}" class="space-y-6">
                            @csrf
                            <!-- Step 1: Batch -->
                            <div wizard-step style="display:block;">
                                <div class="flex items-center gap-2 px-3 py-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg mb-4 w-fit">
                                    <i class="fas fa-calendar-alt text-blue-600 dark:text-blue-400 text-lg"></i>
                                    <h4 class="text-lg font-semibold text-blue-900 dark:text-blue-200">{{ __('Create Academic Batch') }}</h4>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <div>
                                        <label for="batch_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Batch Name</label>
                                        <input type="text" name="batch_name" id="batch_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                        <small class="block mt-1 text-gray-500"><i class="fas fa-lightbulb text-yellow-500 mr-1"></i> Format: <strong>(2025-2026)</strong> - Use year range format</small>
                                    </div>
                                    <div>
                                        <label for="batch_start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                                        <input type="date" name="batch_start_date" id="batch_start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    </div>
                                    <div>
                                        <label for="batch_end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                                        <input type="date" name="batch_end_date" id="batch_end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    </div>
                                </div>
                                <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded mt-4">
                                    <p class="text-blue-900 text-sm"><i class="fas fa-info-circle mr-2"></i> An academic batch represents a school year. This will be the active batch for all academic activities.</p>
                                </div>
                                <div class="flex justify-end mt-6">
                                    <button class="wizard-next inline-flex justify-center items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition-colors" type="button">Next <i class="fas fa-arrow-right ml-2"></i></button>
                                </div>
                            </div>
                            <!-- Step 2: Grades & Classes -->
                            <div wizard-step style="display:none;">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-2 px-3 py-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                        <i class="fas fa-layer-group text-blue-600 dark:text-blue-400 text-lg"></i>
                                        <h4 class="text-lg font-semibold text-blue-900 dark:text-blue-200">{{ __('Setup Grades & Classes') }}</h4>
                                    </div>
                                    <button type="button" class="wizard-add-grade bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 text-sm font-semibold transition-colors"><i class="fas fa-plus"></i> {{ __('Add Grade & Classes') }}</button>
                                </div>
                                <div id="gradesClassesList" class="mb-4">
                                    <!-- Dynamic grade/class cards will be added here -->
                                </div>
                                <template id="gradeClassTemplate">
                                    <div class="grade-class-card bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 mb-4 border border-gray-200 dark:border-gray-700 relative">
                                        <!-- Delete Button - Top Right -->
                                        <button type="button" class="wizard-remove-grade absolute top-3 right-3 w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                        
                                        <!-- Form Fields - Single Column -->
                                        <div class="space-y-4 pr-8">
                                            <!-- Grade Level -->
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                    <i class="fas fa-layer-group text-blue-500 mr-1"></i>Grade Level
                                                </label>
                                                <select name="grade_level[]" class="grade-level-dropdown w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-blue-500 focus:ring-blue-500" required>
                                                    <option value="">Select Level</option>
                                                    <option value="0">Grade 0 (Kindergarten)</option>
                                                    <option value="1">Grade 1</option>
                                                    <option value="2">Grade 2</option>
                                                    <option value="3">Grade 3</option>
                                                    <option value="4">Grade 4</option>
                                                    <option value="5">Grade 5</option>
                                                    <option value="6">Grade 6</option>
                                                    <option value="7">Grade 7</option>
                                                    <option value="8">Grade 8</option>
                                                    <option value="9">Grade 9</option>
                                                    <option value="10">Grade 10</option>
                                                    <option value="11">Grade 11</option>
                                                    <option value="12">Grade 12</option>
                                                </select>
                                            </div>
                                            
                                            <!-- Grade Category -->
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                    <i class="fas fa-tag text-purple-500 mr-1"></i>Grade Category
                                                </label>
                                                <select name="grade_category_id[]" class="grade-category-dropdown w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-blue-500 focus:ring-blue-500" required>
                                                    <option value="">Select Category</option>
                                                    @foreach(App\Models\GradeCategory::all() as $cat)
                                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <!-- Classes -->
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                    <i class="fas fa-chalkboard text-green-500 mr-1"></i>Classes
                                                </label>
                                                <select name="grade_classes[]" class="grade-classes-dropdown w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-blue-500 focus:ring-blue-500" multiple required>
                                                    <option value="A">Class A</option>
                                                    <option value="B">Class B</option>
                                                    <option value="C">Class C</option>
                                                    <option value="D">Class D</option>
                                                    <option value="E">Class E</option>
                                                    <option value="F">Class F</option>
                                                    <option value="G">Class G</option>
                                                    <option value="H">Class H</option>
                                                    <option value="I">Class I</option>
                                                    <option value="J">Class J</option>
                                                    <option value="K">Class K</option>
                                                    <option value="L">Class L</option>
                                                    <option value="M">Class M</option>
                                                    <option value="N">Class N</option>
                                                    <option value="O">Class O</option>
                                                    <option value="P">Class P</option>
                                                    <option value="Q">Class Q</option>
                                                    <option value="R">Class R</option>
                                                    <option value="S">Class S</option>
                                                    <option value="T">Class T</option>
                                                    <option value="U">Class U</option>
                                                    <option value="V">Class V</option>
                                                    <option value="W">Class W</option>
                                                    <option value="X">Class X</option>
                                                    <option value="Y">Class Y</option>
                                                    <option value="Z">Class Z</option>
                                                </select>
                                                <small class="block mt-2 text-xs text-gray-500 dark:text-gray-400">
                                                    <i class="fas fa-info-circle mr-1"></i>Select multiple classes for this grade (e.g., A, B, C)
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded mt-4">
                                    <p class="text-blue-900 text-sm"><i class="fas fa-info-circle mr-2"></i> Add grades and specify the number of classes for each grade. Each grade can have a different number of classes.</p>
                                </div>
                                <div class="flex justify-between mt-6">
                                    <button class="wizard-prev inline-flex justify-center items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg shadow-md transition-colors" type="button"><i class="fas fa-arrow-left mr-2"></i> Previous</button>
                                    <button class="wizard-next inline-flex justify-center items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition-colors" type="button">Next <i class="fas fa-arrow-right ml-2"></i></button>
                                </div>
                            </div>
                            <!-- Step 3: Rooms -->
                            <div wizard-step style="display:none;">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-2 px-3 py-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                        <i class="fas fa-door-open text-blue-600 dark:text-blue-400 text-lg"></i>
                                        <h4 class="text-lg font-semibold text-blue-900 dark:text-blue-200">{{ __('Setup Rooms') }}</h4>
                                    </div>
                                    <button type="button" class="wizard-add-room bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 text-sm font-semibold transition-colors"><i class="fas fa-plus"></i> {{ __('Add Room') }}</button>
                                </div>
                                <div id="roomsList" class="mb-4 space-y-3">
                                    <!-- Dynamic room cards will be added here -->
                                </div>
                                <template id="roomTemplate">
                                    <div class="room-card bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 border border-gray-200 dark:border-gray-700 relative">
                                        <!-- Delete Button - Top Right -->
                                        <button type="button" class="wizard-remove-room absolute top-3 right-3 w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                        
                                        <!-- Form Fields - Single Column -->
                                        <div class="space-y-4 pr-8">
                                            <!-- Room Name -->
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                    <i class="fas fa-door-open text-blue-500 mr-1"></i>{{ __('Room Name') }}
                                                </label>
                                                <input type="text" name="room_name[]" class="room-name-input w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-blue-500 focus:ring-blue-500" placeholder="e.g., Room 101" required>
                                            </div>
                                            
                                            <!-- Building -->
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                    <i class="fas fa-building text-purple-500 mr-1"></i>{{ __('Building') }}
                                                </label>
                                                <input type="text" name="room_building[]" class="room-building-input w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-blue-500 focus:ring-blue-500" placeholder="e.g., Main Building" required>
                                            </div>
                                            
                                            <!-- Floor -->
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                    <i class="fas fa-layer-group text-indigo-500 mr-1"></i>{{ __('Floor') }}
                                                </label>
                                                <input type="number" name="room_floor[]" class="room-floor-input w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-blue-500 focus:ring-blue-500" placeholder="e.g., 1" min="0" required>
                                                <small class="block mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    <i class="fas fa-info-circle mr-1"></i>Ground floor = 0, First floor = 1, etc.
                                                </small>
                                            </div>
                                            
                                            <!-- Capacity -->
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                    <i class="fas fa-users text-green-500 mr-1"></i>{{ __('Capacity') }}
                                                </label>
                                                <input type="number" name="room_capacity[]" class="room-capacity-input w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-blue-500 focus:ring-blue-500" placeholder="e.g., 40" min="1" required>
                                                <small class="block mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    <i class="fas fa-info-circle mr-1"></i>Maximum number of students
                                                </small>
                                            </div>
                                            
                                            <!-- Facilities -->
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                    <i class="fas fa-tools text-orange-500 mr-1"></i>{{ __('Facilities') }}
                                                </label>
                                                <select name="room_facilities[]" class="room-facilities-dropdown w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-blue-500 focus:ring-blue-500" multiple required>
                                                    @foreach(App\Models\Facility::all() as $facility)
                                                        <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                                                    @endforeach
                                                </select>
                                                <small class="block mt-2 text-xs text-gray-500 dark:text-gray-400">
                                                    <i class="fas fa-info-circle mr-1"></i>Select multiple facilities available in this room
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-600 dark:border-blue-400 p-4 rounded mt-4">
                                    <p class="text-blue-900 dark:text-blue-200 text-sm"><i class="fas fa-info-circle mr-2"></i> {{ __('Add rooms with custom names, capacity, type, and select facilities.') }}</p>
                                </div>
                                <div class="flex justify-between mt-6">
                                    <button class="wizard-prev inline-flex justify-center items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg shadow-md transition-colors" type="button"><i class="fas fa-arrow-left mr-2"></i> {{ __('Previous') }}</button>
                                    <button class="wizard-next inline-flex justify-center items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition-colors" type="button">{{ __('Next') }} <i class="fas fa-arrow-right ml-2"></i></button>
                                </div>
                            </div>
                            <!-- Step 4: Subjects -->
                            <div wizard-step style="display:none;">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-2 px-3 py-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                        <i class="fas fa-book text-blue-600 dark:text-blue-400 text-lg"></i>
                                        <h4 class="text-lg font-semibold text-blue-900 dark:text-blue-200">{{ __('Setup Subjects') }}</h4>
                                    </div>
                                    <button type="button" class="wizard-add-subject bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 text-sm font-semibold transition-colors"><i class="fas fa-plus"></i> {{ __('Add Subject') }}</button>
                                </div>
                                <div id="subjectsList" class="mb-4 space-y-4">
                                    <!-- Dynamic subject cards will be added here -->
                                </div>
                                <template id="subjectTemplate">
                                    <div class="subject-card bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 border border-gray-200 dark:border-gray-700 relative">
                                        <!-- Delete Button - Top Right -->
                                        <button type="button" class="wizard-remove-subject absolute top-3 right-3 w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                        
                                        <!-- Form Fields - Single Column -->
                                        <div class="space-y-4 pr-8">
                                            <!-- Subject Name -->
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                    <i class="fas fa-book text-blue-500 mr-1"></i>{{ __('Subject Name') }}
                                                </label>
                                                <input type="text" name="subject_name[]" class="subject-name-input w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-blue-500 focus:ring-blue-500" placeholder="e.g., Mathematics" required>
                                            </div>
                                            
                                            <!-- Subject Code -->
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                    <i class="fas fa-hashtag text-purple-500 mr-1"></i>{{ __('Subject Code') }}
                                                </label>
                                                <input type="text" name="subject_code[]" class="subject-code-input w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-blue-500 focus:ring-blue-500" placeholder="e.g., MATH101" required>
                                                <small class="block mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    <i class="fas fa-info-circle mr-1"></i>Unique identifier for this subject
                                                </small>
                                            </div>
                                            
                                            <!-- Subject Type -->
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                    <i class="fas fa-tag text-green-500 mr-1"></i>{{ __('Subject Type') }}
                                                </label>
                                                <select name="subject_type_id[]" class="subject-type-dropdown w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-blue-500 focus:ring-blue-500" required>
                                                    <option value="">{{ __('Select Type') }}</option>
                                                    @foreach(App\Models\SubjectType::all() as $type)
                                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                    @endforeach
                                                </select>
                                                <small class="block mt-2 text-xs text-gray-500 dark:text-gray-400">
                                                    <i class="fas fa-info-circle mr-1"></i>Choose whether this is a core or elective subject
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-600 dark:border-blue-400 p-4 rounded mt-4">
                                    <p class="text-blue-900 dark:text-blue-200 text-sm"><i class="fas fa-info-circle mr-2"></i> {{ __('Add subjects for each grade using the plus button. You can create core subjects and elective subjects.') }}</p>
                                </div>
                                <div class="flex justify-between mt-6">
                                    <button class="wizard-prev inline-flex justify-center items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg shadow-md transition-colors" type="button"><i class="fas fa-arrow-left mr-2"></i> {{ __('Previous') }}</button>
                                    <button class="wizard-submit inline-flex justify-center items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition-colors" type="submit"><i class="fas fa-check mr-2"></i> {{ __('Complete Setup') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- Sidebar with Tips -->
                    <div class="hidden lg:block w-72 flex-shrink-0">
                        <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-5 sticky top-6">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ __('Setup Guide') }}</h3>
                            <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check-circle text-blue-500 mt-0.5"></i>
                                    <span>{{ __('Complete each step to build your academic structure') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check-circle text-blue-500 mt-0.5"></i>
                                    <span>{{ __('You can skip steps and complete them later') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check-circle text-blue-500 mt-0.5"></i>
                                    <span>{{ __('All data can be edited after setup') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check-circle text-blue-500 mt-0.5"></i>
                                    <span>{{ __('Return to this page anytime to reset') }}</span>
                                </li>
                            </ul>
                            <div class="mt-5 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-info-circle text-blue-500"></i>
                                    <span>{{ __('Need help? Contact the admin support team') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                <script src="/js/wizard.js"></script>
                            <!-- jQuery (required for Select2) -->
                            <script src="/js/jquery-3.6.0.min.js"></script>
                            <!-- Select2 JS -->
                            <script src="/js/select2.min.js"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                            const wizardSteps = document.querySelectorAll('.wizard-step-item');
                            function highlightStep(step) {
                                wizardSteps.forEach((el) => {
                                    const indicator = el.querySelector('.step-indicator');
                                    const label = el.querySelector('.step-label');
                                    const current = parseInt(el.getAttribute('data-step'));
                                    const activeBg = el.getAttribute('data-active-bg') || 'bg-blue-600';
                                    const activeLabel = el.getAttribute('data-active-label') || 'text-blue-900 dark:text-blue-200';
                                    
                                    if (current === step) {
                                        // Active step - use step-specific color
                                        indicator.className = 'step-indicator rounded-full w-10 h-10 flex items-center justify-center mb-2 ' + activeBg + ' text-white';
                                        label.className = 'step-label text-sm ' + activeLabel + ' font-semibold';
                                    } else {
                                        // Inactive steps
                                        indicator.className = 'step-indicator rounded-full w-10 h-10 flex items-center justify-center mb-2 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400';
                                        label.className = 'step-label text-sm text-gray-600 dark:text-gray-400';
                                    }
                                });
                            }
                            let currentStep = 1;
                            function setStep(step) {
                                currentStep = step;
                                highlightStep(step);
                            }
                            const wizard = new Wizard('#wizardForm', {
                                stepSelector: '[wizard-step]',
                                nextBtn: '.wizard-next',
                                prevBtn: '.wizard-prev',
                                submitBtn: '.wizard-submit',
                                onStepChange: setStep
                            });
                            highlightStep(currentStep);
                            
                            // Make step indicators clickable
                            wizardSteps.forEach((stepItem) => {
                                stepItem.addEventListener('click', function() {
                                    const targetStep = parseInt(this.getAttribute('data-step'));
                                    wizard.goToStep(targetStep);
                                });
                            });
                            // Dynamic add/remove logic for grades/classes
                            const gradesClassesList = document.getElementById('gradesClassesList');
                            const gradeClassTemplate = document.getElementById('gradeClassTemplate');
                            document.querySelector('.wizard-add-grade').addEventListener('click', function() {
                                const clone = gradeClassTemplate.content.cloneNode(true);
                                gradesClassesList.appendChild(clone);
                                // Initialize Select2 for new class dropdown
                                setTimeout(() => {
                                    $(gradesClassesList).find('.grade-classes-dropdown').last().select2({
                                        width: 'resolve',
                                        placeholder: 'Select classes',
                                        allowClear: true
                                        // tags: true // Uncomment if you want to allow custom entries
                                    });
                                }, 0);
                            });
                            gradesClassesList.addEventListener('click', function(e) {
                                if (e.target.closest('.wizard-remove-grade')) {
                                    e.target.closest('.grade-class-card').remove();
                                }
                            });
                            // Initialize Select2 for any existing class dropdowns on page load
                            $(function() {
                                $('.grade-classes-dropdown').select2({
                                    width: 'resolve',
                                    placeholder: 'Select classes',
                                    allowClear: true
                                    // tags: true // Uncomment if you want to allow custom entries
                                });
                            });
                            // Dynamic add/remove logic for rooms
                            const roomsList = document.getElementById('roomsList');
                            const roomTemplate = document.getElementById('roomTemplate');
                            document.querySelector('.wizard-add-room').addEventListener('click', function() {
                                const clone = roomTemplate.content.cloneNode(true);
                                roomsList.appendChild(clone);
                                // Initialize Select2 for new facilities dropdown
                                setTimeout(() => {
                                    $(roomsList).find('.room-facilities-dropdown').last().select2({
                                        width: 'resolve',
                                        placeholder: 'Select facilities',
                                        allowClear: true
                                    });
                                }, 0);
                            });
                            roomsList.addEventListener('click', function(e) {
                                if (e.target.closest('.wizard-remove-room')) {
                                    e.target.closest('.room-card').remove();
                                }
                            });
                            // Initialize Select2 for any existing facilities dropdowns on page load
                            $(function() {
                                $('.room-facilities-dropdown').select2({
                                    width: 'resolve',
                                    placeholder: 'Select facilities',
                                    allowClear: true
                                });
                            });
                            // Dynamic add/remove logic for subjects
                            const subjectsList = document.getElementById('subjectsList');
                            const subjectTemplate = document.getElementById('subjectTemplate');
                            document.querySelector('.wizard-add-subject').addEventListener('click', function() {
                                const clone = subjectTemplate.content.cloneNode(true);
                                subjectsList.appendChild(clone);
                            });
                            subjectsList.addEventListener('click', function(e) {
                                if (e.target.closest('.wizard-remove-subject')) {
                                    e.target.closest('.subject-card').remove();
                                }
                            });
                    });
                </script>
            </div>
        </div>

    </div>

</x-app-layout>
