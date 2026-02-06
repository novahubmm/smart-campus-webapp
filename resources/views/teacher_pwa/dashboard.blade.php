@extends('pwa.layouts.app', [
    'theme' => 'teacher',
    'title' => 'Teacher Dashboard',
    'headerTitle' => 'Dashboard',
    'activeNav' => 'home',
    'showNotifications' => true,
    'unreadCount' => $unreadCount ?? 0,
    'themeColor' => '#8BC34A',
    'role' => 'teacher'
])

@section('content')
<div x-data="teacherDashboard()">
    {{-- Welcome Card --}}
    <div class="welcome-card">
        <p class="welcome-greeting">Good {{ $greeting ?? 'morning' }},</p>
        <h2 class="welcome-name">{{ $teacher->name ?? 'Teacher' }}</h2>
        <p class="welcome-subtitle">
            <i class="fas fa-chalkboard-teacher"></i> {{ $teacher->position ?? 'Teacher' }}
            @if(isset($teacher->department))
                • {{ $teacher->department }}
            @endif
        </p>
    </div>

    {{-- Quick Stats --}}
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 24px;">
        @include('pwa.components.stat-card', [
            'value' => $stats->today_classes ?? '0',
            'label' => 'Today Classes',
            'icon' => 'fa-chalkboard',
            'color' => '#2196F3',
            'bgColor' => '#E3F2FD'
        ])
        
        @include('pwa.components.stat-card', [
            'value' => $stats->total_students ?? '0',
            'label' => 'Students',
            'icon' => 'fa-user-graduate',
            'color' => '#4CAF50',
            'bgColor' => '#E8F5E9'
        ])
        
        @include('pwa.components.stat-card', [
            'value' => $stats->pending_homework ?? '0',
            'label' => 'Homework',
            'icon' => 'fa-book',
            'color' => '#FFC107',
            'bgColor' => '#FFF8E1'
        ])
    </div>

    {{-- Today's Schedule --}}
    <div class="section-header">
        <h2 class="section-title">Today's Schedule</h2>
        <a href="{{ route('teacher-pwa.timetable') }}" class="section-action">
            View All <i class="fas fa-arrow-right"></i>
        </a>
    </div>

    <template x-if="loading">
        <div class="loading-container">
            <div class="loading-spinner"></div>
        </div>
    </template>

    <template x-if="!loading && todayClasses.length === 0">
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-calendar-times"></i>
            </div>
            <h3 class="empty-state-title">No Classes Today</h3>
            <p class="empty-state-subtitle">You don't have any classes scheduled for today.</p>
        </div>
    </template>

    <template x-if="!loading && todayClasses.length > 0">
        <div>
            <template x-for="classItem in todayClasses" :key="classItem.id">
                <div>
                    @include('pwa.components.class-card', ['class' => []])
                    {{-- Will be populated by Alpine.js --}}
                </div>
            </template>
        </div>
    </template>

    {{-- Quick Actions --}}
    <div class="section-header">
        <h2 class="section-title">Quick Actions</h2>
    </div>

    <div style="display: grid; gap: 8px;">
        @include('pwa.components.list-item', [
            'title' => 'Take Attendance',
            'subtitle' => 'Mark student attendance',
            'icon' => 'fa-clipboard-check',
            'iconBg' => '#E3F2FD',
            'iconColor' => '#2196F3',
            'url' => route('teacher-pwa.attendance')
        ])
        
        @include('pwa.components.list-item', [
            'title' => 'My Classes',
            'subtitle' => 'View all your classes',
            'icon' => 'fa-chalkboard',
            'iconBg' => '#E8F5E9',
            'iconColor' => '#4CAF50',
            'url' => route('teacher-pwa.classes')
        ])
        
        @include('pwa.components.list-item', [
            'title' => 'Homework',
            'subtitle' => 'Manage homework assignments',
            'icon' => 'fa-book',
            'iconBg' => '#FFF8E1',
            'iconColor' => '#FFC107',
            'url' => route('teacher-pwa.homework'),
            'badge' => isset($stats->pending_homework) && $stats->pending_homework > 0 ? [
                'text' => $stats->pending_homework,
                'type' => 'warning'
            ] : null
        ])
        
        @include('pwa.components.list-item', [
            'title' => 'Announcements',
            'subtitle' => 'View school announcements',
            'icon' => 'fa-bullhorn',
            'iconBg' => '#F3E5F5',
            'iconColor' => '#9C27B0',
            'url' => route('teacher-pwa.announcements')
        ])
    </div>
</div>
@endsection

@push('scripts')
<script>
function teacherDashboard() {
    return {
        loading: true,
        todayClasses: @json($todayClasses ?? []),
        
        init() {
            this.loading = false;
            // Fetch today's classes if not provided
            if (this.todayClasses.length === 0) {
                this.fetchTodayClasses();
            }
        },
        
        async fetchTodayClasses() {
            try {
                this.loading = true;
                const response = await fetch('/api/v1/teacher/today-classes', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('teacher_token')}`,
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.todayClasses = data.data?.classes || [];
                }
            } catch (error) {
                console.error('Error fetching classes:', error);
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endpush
