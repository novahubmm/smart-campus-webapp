@extends('pwa.layouts.app', [
    'theme' => 'teacher',
    'title' => 'Homework',
    'headerTitle' => 'Homework',
    'activeNav' => 'homework',
    'role' => 'teacher'
])

@section('content')
<div class="pwa-container">
    <!-- Add Homework Button -->
    <button class="pwa-btn pwa-btn-primary" onclick="showAddHomework()">
        <svg style="width: 20px; height: 20px; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Add Homework
    </button>

    <!-- Filter Tabs -->
    <div class="pwa-tabs">
        <button class="pwa-tab active" data-filter="active">Active</button>
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
                        <p class="pwa-homework-class">{{ $homework->class->name }} • {{ $homework->subject->name }}</p>
                    </div>
                    <span class="pwa-badge pwa-badge-{{ $homework->status }}">{{ ucfirst($homework->status) }}</span>
                </div>
                
                <p class="pwa-homework-description">{{ $homework->description }}</p>
                
                <div class="pwa-homework-meta">
                    <div class="pwa-homework-meta-item">
                        <svg class="pwa-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>Due: {{ $homework->due_date->format('M d, Y') }}</span>
                    </div>
                    <div class="pwa-homework-meta-item">
                        <svg class="pwa-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span>{{ $homework->submissions_count }}/{{ $homework->total_students }} submitted</span>
                    </div>
                </div>
                
                <div class="pwa-homework-actions">
                    <button class="pwa-btn pwa-btn-secondary" onclick="viewSubmissions({{ $homework->id }})">View Submissions</button>
                    <button class="pwa-btn pwa-btn-outline" onclick="editHomework({{ $homework->id }})">Edit</button>
                </div>
            </div>
        @empty
            <div class="pwa-empty-state">
                <svg class="pwa-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="pwa-empty-text">No homework assigned yet</p>
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

.pwa-homework-class {
    font-size: 14px;
    color: #6E6E73;
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
    margin-bottom: 16px;
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

.pwa-homework-actions {
    display: flex;
    gap: 8px;
}

.pwa-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.pwa-badge-active {
    background: #E8F5E9;
    color: #2E7D32;
}

.pwa-badge-completed {
    background: #E3F2FD;
    color: #1976D2;
}

.pwa-badge-overdue {
    background: #FFEBEE;
    color: #C62828;
}
</style>

<script>
function showAddHomework() {
    alert('Add homework form will open here');
}

function viewSubmissions(id) {
    alert('View submissions for homework ' + id);
}

function editHomework(id) {
    alert('Edit homework ' + id);
}
</script>
@endsection
