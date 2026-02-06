@extends('pwa.layouts.app', [
    'theme' => 'teacher',
    'title' => 'Students',
    'headerTitle' => 'Students',
    'activeNav' => 'students',
    'role' => 'teacher'
])

@section('content')
<div class="pwa-container">
    <!-- Search Bar -->
    <div class="pwa-search-bar">
        <svg class="pwa-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
        <input type="text" class="pwa-search-input" placeholder="Search students..." id="student-search">
    </div>

    <!-- Class Filter -->
    <div class="pwa-card">
        <div class="pwa-form-group">
            <label class="pwa-label">Filter by Class</label>
            <select class="pwa-select" id="class-filter">
                <option value="">All Classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Students List -->
    <div class="pwa-section">
        @forelse($students as $student)
            <div class="pwa-student-card" data-class="{{ $student->class_id }}" data-name="{{ strtolower($student->name) }}">
                <img src="{{ $student->profile_image ?? asset('images/student_default_profile.jpg') }}" alt="{{ $student->name }}" class="pwa-student-photo">
                <div class="pwa-student-info">
                    <h3 class="pwa-student-name">{{ $student->name }}</h3>
                    <p class="pwa-student-details">{{ $student->student_id }} • {{ $student->class->name }}</p>
                    <div class="pwa-student-stats">
                        <span class="pwa-stat-badge">
                            <svg class="pwa-stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ $student->attendance_rate }}% Attendance
                        </span>
                        <span class="pwa-stat-badge">
                            <svg class="pwa-stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            {{ $student->homework_completion }}% Homework
                        </span>
                    </div>
                </div>
                <button class="pwa-student-action" onclick="viewStudent({{ $student->id }})">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        @empty
            <div class="pwa-empty-state">
                <svg class="pwa-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <p class="pwa-empty-text">No students found</p>
            </div>
        @endforelse
    </div>
</div>

<style>
.pwa-student-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: white;
    border-radius: 12px;
    margin-bottom: 12px;
}

.pwa-student-photo {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
}

.pwa-student-info {
    flex: 1;
}

.pwa-student-name {
    font-size: 16px;
    font-weight: 600;
    color: #1C1C1E;
    margin: 0 0 4px 0;
}

.pwa-student-details {
    font-size: 13px;
    color: #6E6E73;
    margin: 0 0 8px 0;
}

.pwa-student-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.pwa-stat-badge {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    background: #F7F9FC;
    border-radius: 6px;
    font-size: 12px;
    color: #6E6E73;
}

.pwa-stat-icon {
    width: 14px;
    height: 14px;
}

.pwa-student-action {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #F7F9FC;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.pwa-student-action svg {
    width: 20px;
    height: 20px;
    color: #6E6E73;
}
</style>

<script>
// Search functionality
document.getElementById('student-search').addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    document.querySelectorAll('.pwa-student-card').forEach(card => {
        const name = card.dataset.name;
        card.style.display = name.includes(search) ? 'flex' : 'none';
    });
});

// Class filter
document.getElementById('class-filter').addEventListener('change', function(e) {
    const classId = e.target.value;
    document.querySelectorAll('.pwa-student-card').forEach(card => {
        if (!classId || card.dataset.class === classId) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
});

function viewStudent(id) {
    alert('View student details for ID: ' + id);
}
</script>
@endsection
