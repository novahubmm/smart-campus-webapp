{{-- Class Card Component --}}
@php
    $class = $class ?? [];
    $status = $class['status'] ?? 'upcoming';
    $statusColors = [
        'completed' => ['bg' => '#E8F5E9', 'text' => '#4CAF50'],
        'ongoing' => ['bg' => '#E3F2FD', 'text' => '#2196F3'],
        'upcoming' => ['bg' => '#FFF8E1', 'text' => '#FFC107'],
    ];
    $color = $statusColors[$status] ?? $statusColors['upcoming'];
@endphp

<div class="class-card" onclick="window.location='{{ $class['url'] ?? '#' }}'">
    <div class="class-card-header">
        <div class="class-card-time">
            <i class="fas fa-clock"></i> {{ $class['time'] ?? '00:00' }}
        </div>
        <div class="class-card-status" style="background: {{ $color['bg'] }}; color: {{ $color['text'] }};">
            {{ ucfirst($status) }}
        </div>
    </div>
    
    <h3 class="class-card-title">{{ $class['subject'] ?? 'Subject' }}</h3>
    <p class="class-card-subtitle">
        <i class="fas fa-users"></i> {{ $class['class_name'] ?? 'Class' }}
        @if(isset($class['room']))
            • <i class="fas fa-door-open"></i> {{ $class['room'] }}
        @endif
    </p>
    
    @if(isset($class['students_count']))
        <div style="margin-top: 8px; font-size: 14px; color: var(--text-secondary);">
            <i class="fas fa-user-graduate"></i> {{ $class['students_count'] }} students
        </div>
    @endif
</div>
