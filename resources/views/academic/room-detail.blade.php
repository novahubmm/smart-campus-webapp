<x-app-layout>
    <x-slot name="styles">
        <link rel="stylesheet" href="{{ asset('css/academic-management.css') }}">
    </x-slot>
    <x-slot name="header">
        <x-page-header
            icon="fas fa-door-open"
            iconBg="bg-amber-50 dark:bg-amber-900/30"
            iconColor="text-amber-700 dark:text-amber-200"
            :subtitle="__('academic_management.Academic Management')"
            :title="__('academic_management.Room Details')"
        />
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-back-link 
                :href="route('academic-management.index', ['tab' => 'rooms'])"
                :text="__('academic_management.Back to Academic Management')"
            />

            <x-detail-header
                icon="fas fa-door-open"
                iconBg="bg-amber-50 dark:bg-amber-900/30"
                iconColor="text-amber-600 dark:text-amber-400"
                :title="$room->name"
                :subtitle="($room->building ?? 'Building A') . ' • ' . ($room->floor ?? '1st Floor')"
                :badge="ucfirst($room->status ?? 'Available')"
                badgeColor="active"
                :editRoute="null"
                :deleteRoute="route('academic-management.rooms.destroy', $room->id)"
                :deleteText="__('academic_management.Delete Room')"
                :deleteTitle="__('academic_management.Delete Room')"
                :deleteMessage="__('academic_management.Are you sure you want to delete this room? This action cannot be undone.')"
            >
                <x-slot name="actions">
                    <button type="button" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors" onclick="openModal('editRoomModal')">
                        <i class="fas fa-edit"></i>
                        <span>{{ __('academic_management.Edit Room') }}</span>
                    </button>
                </x-slot>
            </x-detail-header>



            <x-info-table 
                :title="__('academic_management.Room Information')"
                :rows="[
                    [
                        'label' => __('academic_management.Room Number'),
                        'value' => e($room->name)
                    ],
                    [
                        'label' => __('academic_management.Building'),
                        'value' => e($room->building ?? '—')
                    ],
                    [
                        'label' => __('academic_management.Floor'),
                        'value' => e($room->floor ?? '—')
                    ],
                    [
                        'label' => __('academic_management.Capacity'),
                        'value' => e($room->capacity ?? '—')
                    ],
                ]"
            />

            <x-info-table 
                :title="__('academic_management.Room Facilities')"
                :rows="[
                    [
                        'label' => __('academic_management.Facilities'),
                        'value' => $room->facilities->isNotEmpty() ? e($room->facilities->pluck('name')->implode(', ')) : '—'
                    ],
                ]"
            />

            @php
                $classColumns = [
                    [
                        'label' => __('academic_management.Class Name'),
                        'render' => fn($class) => '<a href="' . route('academic-management.classes.show', $class->id) . '" style="font-weight: 600; color: #007AFF;">' . e($class->name) . '</a>',
                    ],
                    [
                        'label' => __('academic_management.Grade'),
                        'render' => fn($class) => __('academic_management.Grade') . ' ' . e($class->grade->level ?? '—'),
                    ],
                    [
                        'label' => __('academic_management.Students'),
                        'render' => fn($class) => e($class->students->count() ?? 0),
                    ],
                    [
                        'label' => __('academic_management.Teacher'),
                        'render' => fn($class) => e($class->teacher?->user?->name ?? '—'),
                    ],
                ];
            @endphp

            <x-data-table
                :title="__('academic_management.Assigned Classes')"
                :columns="$classColumns"
                :data="$room->classes"
                :actions="[]"
                :show-filters="false"
                table-class="basic-table"
            />

            <x-form-modal 
                id="editRoomModal" 
                title="{{ __('academic_management.Edit Room') }}" 
                icon="fas fa-door-open"
                action="{{ route('academic-management.rooms.update', $room->id) }}"
                method="PUT"
                :submitText="__('academic_management.Update Room')"
                :cancelText="__('academic_management.Cancel')">
                @include('academic.partials.room-form-fields', ['room' => $room])
            </x-form-modal>
        </div>
    </div>
</x-app-layout>
