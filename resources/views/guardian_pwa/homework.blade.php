@extends('pwa.layouts.app', [
    'theme' => 'guardian',
    'title' => 'Homework',
    'headerTitle' => 'Homework',
    'activeNav' => 'homework',
    'role' => 'guardian'
])

@section('content')
<div class="pwa-container">
    <!-- Student Selector -->
    <div class="pwa-card">
        <div class="pwa-form-group">
            <label class="pwa-label">Select Student</label>
            <select class="pwa-select" id="student-selector">
                @foreach($students as $student)
                    <option value="{{ $student->id }}">{{ $student->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Homework Stats -->
    <div class="pwa-stats-grid">
        @include('pwa.components.stat-card', [
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>',
            'value' => $selectedStudent->total_homework,
            'label' => 'Total Homework',
            'color' => '#26BFFF'
        ])
        @include('pwa.components.stat-card', [
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
            'value' => $selectedStudent->completed_homework,
            'label' => 'Completed',
            'color' => '#34C759'
        ])
        @include('pwa.components.stat-card', [
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
            'value' => $selectedStudent->pending_homework,
            'label' => 'Pending',
            'color' => '#FF9500'
        ])
        @include('pwa.components.stat-card', [
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>',
            'value' => $selectedStudent->overdue_homework,
            'label' => 'Overdue',
            'color' => '#FF3B30'
        ])
    </div>

    <!-- Filter Tabs -->
    <div class="pwa-tabs">
        <button class="pwa-tab active" data-filter="all">All</button>
        <button class="pwa-tab" data-filter="pending">Pending</button>
        <button class="pwa-tab" data-filter="completed">Completed</button>
        <button class="pwa-tab" data-filter="overdue">Overdue</button>
    </div>

    <!-- Homework List -->
    <div class="pwa-section">
        @forelse($homeworks as $homework)
            <div class="pwa-card" data-filter="{{ $homework->status }}">
                <div class="pwa-homework-header">
                    <div>
                        <h3 class="pwa-homework-title">{{ $homework->title }}</h3>
                        <p class="pwa-homework-subject">{{ $homework->subject->name }}</p>
                    </div>
                    <span class="pwa-badge pwa-badge-{{ $homework->status }}">{{ ucfirst($homework->status) }}</span>
                </div>
                
                <p class="pwa-homework-description">{{ $homework->description }}</p>
                
                <div class="pwa-homework-meta">
                    <div class="pwa-homework-meta-item">
                        <svg class="pwa-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>Assigned: {{ $homework->assigned_date->format('M d, Y') }}</span>
                    </div>
                    <div class="pwa-homework-meta-item">
                        <svg class="pwa-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Due: {{ $homework->due_date->format('M d, Y') }}</span>
                    </div>
                </div>
                
                @if($homework->submission)
                    <div class="pwa-submission-info">
                        <svg class="pwa-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Submitted on {{ $homework->submission->submitted_at->format('M d, Y') }}</span>
                    </div>
                @endif
            </div>
        @empty
            <div class="pwa-empty-state">
                <svg class="pwa-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="pwa-empty-text">No homework assigned</p>
            </div>
        @endforelse
    </div>
</div>

<style>
.pwa-homework-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}

.pwa-homework-title {
    font-size: 16px;
    font-weight: 600;
    color: #1C1C1E;
    margin: 0 0 4px 0;
}

.pwa-homework-subject {
    font-size: 14px;
    color: #26BFFF;
    font-weight: 600;
    margin: 0;
}

.pwa-homework-description {
    font-size: 14px;
    color: #1C1C1E;
    margin: 0 0 12px 0;
    line-height: 1.5;
}

.pwa-homework-meta {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding-top: 12px;
    border-top: 1px solid #E5E5EA;
}

.pwa-homework-meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #6E6E73;
}

.pwa-submission-info {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 12px;
    padding: 12px;
    background: #E8F5E9;
    border-radius: 8px;
    font-size: 13px;
    color: #2E7D32;
    font-weight: 600;
}

.pwa-badge-pending {
    background: #FFF3E0;
    color: #E65100;
}

.pwa-badge-completed {
    background: #E8F5E9;
    color: #2E7D32;
}

.pwa-badge-overdue {
    background: #FFEBEE;
    color: #C62828;
}
</style>

<script>
document.getElementById('student-selector').addEventListener('change', function() {
    window.location.href = '{{ route("guardian-pwa.homework") }}?student=' + this.value;
});
</script>
@endsection
