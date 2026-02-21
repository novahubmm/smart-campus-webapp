@props([
    'grades' => collect(),
    'rooms' => collect(),
    'teachers' => collect(),
    'class' => null,
])

@php
    $selectedGrade = old('grade_id', $class?->grade_id);
    $selectedRoom = old('room_id', $class?->room_id);
    $selectedTeacher = old('teacher_id', $class?->teacher_id);
@endphp

<div class="space-y-1">
    <label for="classGrade" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
        {{ __('academic_management.Grade') }} <span class="text-red-500">*</span>
    </label>
    <select 
        id="classGrade" 
        name="grade_id" 
        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
        required>
        @foreach($grades as $grade)
            @php
                $gradeId = is_array($grade) ? $grade['id'] : $grade->id;
                $gradeLevel = is_array($grade) ? $grade['level'] : $grade->level;
            @endphp
            <option value="{{ $gradeId }}" {{ $selectedGrade == $gradeId ? 'selected' : '' }}>
                @gradeName($gradeLevel)
            </option>
        @endforeach
    </select>
    @error('grade_id')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="space-y-1">
    <label for="className" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
        {{ __('academic_management.Class Name') }} <span class="text-red-500">*</span>
    </label>
    <select 
        id="className" 
        name="name" 
        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
        required>
        @foreach(range('A', 'Z') as $letter)
            <option value="{{ $letter }}" {{ strtoupper(old('name', $class->name ?? '')) === $letter ? 'selected' : '' }}>
                {{ $letter }}
            </option>
        @endforeach
    </select>
    @error('name')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="space-y-1">
    <label for="classRoom" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
        {{ __('academic_management.Room') }}
    </label>
    <select 
        id="classRoom" 
        name="room_id" 
        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500">
        @foreach($rooms as $room)
            <option value="{{ $room->id }}" {{ $selectedRoom == $room->id ? 'selected' : '' }}>
                {{ $room->name }}
            </option>
        @endforeach
    </select>
    @error('room_id')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="space-y-1">
    <label for="classTeacher" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
        {{ __('academic_management.Class Teacher') }}
    </label>
    <select 
        id="classTeacher" 
        name="teacher_id" 
        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500">
        @foreach($teachers as $teacher)
            <option value="{{ $teacher->id }}" {{ $selectedTeacher == $teacher->id ? 'selected' : '' }}>
                {{ $teacher->display_name }}
            </option>
        @endforeach
    </select>
    @error('teacher_id')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
