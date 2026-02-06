@extends('pwa.layouts.app', [
    'theme' => 'guardian',
    'title' => $student->name,
    'headerTitle' => $student->name,
    'showBack' => true,
    'hideBottomNav' => true,
    'role' => 'guardian'
])

@section('content')
<div class="pwa-container">
    <!-- Student Header -->
    <div class="pwa-student-header">
        <img src="{{ $student->profile_image ?? asset('images/student_default_profile.jpg') }}" alt="{{ $student->name }}" class="pwa-student-header-photo">
        <h2 class="pwa-student-header-name">{{ $student->name }}</h2>
        <p class="pwa-student-header-id">{{ $student->student_id }}</p>
        <p class="pwa-student-header-class">{{ $student->grade }} • {{ $student->section }}</p>
    </div>

    <!-- Quick Stats -->
    <div class="pwa-stats-grid">
        @include('pwa.components.stat-card', [
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
            'value' => $student->attendance_rate . '%',
            'label' => 'Attendance',
            'color' => '#34C759'
        ])
        @include('pwa.components.stat-card', [
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>',
            'value' => $student->homework_completion . '%',
            'label' => 'Homework',
            'color' => '#26BFFF'
        ])
    </div>

    <!-- Quick Actions -->
    <div class="pwa-section">
        <h3 class="pwa-section-title">Quick Actions</h3>
        <div class="pwa-quick-actions">
            <button class="pwa-action-btn" onclick="window.location.href='{{ route('guardian-pwa.attendance') }}?student={{ $student->id }}'">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <span>Attendance</span>
            </button>
            <button class="pwa-action-btn" onclick="window.location.href='{{ route('guardian-pwa.homework') }}?student={{ $student->id }}'">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Homework</span>
            </button>
            <button class="pwa-action-btn" onclick="window.location.href='{{ route('guardian-pwa.timetable') }}?student={{ $student->id }}'">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span>Timetable</span>
            </button>
            <button class="pwa-action-btn" onclick="window.location.href='{{ route('guardian-pwa.fees') }}?student={{ $student->id }}'">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span>Fees</span>
            </button>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="pwa-section">
        <h3 class="pwa-section-title">Recent Activity</h3>
        @foreach($recentActivity as $activity)
            <div class="pwa-list-item">
                <div class="pwa-list-content">
                    <div class="pwa-list-title">{{ $activity->title }}</div>
                    <div class="pwa-list-subtitle">{{ $activity->created_at->diffForHumans() }}</div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<style>
.pwa-student-header {
    text-align: center;
    padding: 24px;
    background: white;
    border-radius: 16px;
    margin-bottom: 16px;
}

.pwa-student-header-photo {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 16px;
}

.pwa-student-header-name {
    font-size: 24px;
    font-weight: 700;
    color: #1C1C1E;
    margin: 0 0 4px 0;
}

.pwa-student-header-id {
    font-size: 14px;
    color: #6E6E73;
    margin: 0 0 4px 0;
}

.pwa-student-header-class {
    font-size: 14px;
    color: #26BFFF;
    font-weight: 600;
    margin: 0;
}
</style>
@endsection
