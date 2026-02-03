@props(['room' => null])

<div class="space-y-1">
    <label for="roomName" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
        {{ __('academic_management.Room Name') }} <span class="text-red-500">*</span>
    </label>
    <input 
        type="text" 
        id="roomName" 
        name="name" 
        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" 
        placeholder="{{ __('academic_management.e.g., Room 101') }}"
        value="{{ old('name', $room->name ?? '') }}"
        required>
    @error('name')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="space-y-1">
    <label for="roomBuilding" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
        {{ __('academic_management.Building') }}
    </label>
    <input 
        type="text" 
        id="roomBuilding" 
        name="building" 
        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" 
        placeholder="{{ __('academic_management.e.g., Building A') }}"
        value="{{ old('building', $room->building ?? '') }}">
    @error('building')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="space-y-1">
    <label for="roomFloor" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
        {{ __('academic_management.Floor') }}
    </label>
    <input 
        type="text" 
        id="roomFloor" 
        name="floor" 
        class="w-full rounded-lg border-gray-300 dark-border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" 
        placeholder="{{ __('academic_management.e.g., 1st Floor') }}"
        value="{{ old('floor', $room->floor ?? '') }}">
    @error('floor')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="space-y-1">
    <label for="roomCapacity" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
        {{ __('academic_management.Capacity') }}
    </label>
    <input 
        type="number" 
        id="roomCapacity" 
        name="capacity" 
        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" 
        value="{{ old('capacity', $room->capacity ?? '') }}">
    @error('capacity')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
