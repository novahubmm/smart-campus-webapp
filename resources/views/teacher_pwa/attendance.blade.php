@extends('pwa.layouts.app', [
    'theme' => 'teacher',
    'title' => 'Attendance',
    'headerTitle' => 'Attendance',
    'activeNav' => 'attendance',
    'role' => 'teacher'
])

@section('content')
<div class="pwa-container">
    <!-- Date Selector -->
    <div class="pwa-card">
        <div class="pwa-form-group">
            <label class="pwa-label">Select Date</label>
            <input type="date" class="pwa-input" id="attendance-date" value="{{ date('Y-m-d') }}">
        </div>
    </div>

    <!-- Class Selection -->
    <div class="pwa-section">
        <h3 class="pwa-section-title">Select Class</h3>
        @forelse($classes as $class)
            <div class="pwa-list-item" onclick="window.location.href='{{ route('teacher-pwa.attendance') }}?class={{ $class->id }}&date=' + document.getElementById('attendance-date').value">
                <div class="pwa-list-content">
                    <div class="pwa-list-title">{{ $class->name }}</div>
                    <div class="pwa-list-subtitle">{{ $class->grade->name }} • {{ $class->students_count }} students</div>
                </div>
                <svg class="pwa-list-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        @empty
            <div class="pwa-empty-state">
                <svg class="pwa-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <p class="pwa-empty-text">No classes assigned</p>
            </div>
        @endforelse
    </div>

    @if(request('class'))
    <!-- Attendance Taking -->
    <div class="pwa-section">
        <h3 class="pwa-section-title">Mark Attendance</h3>
        <form id="attendance-form">
            @foreach($students as $student)
            <div class="pwa-attendance-item">
                <div class="pwa-student-info">
                    <img src="{{ $student->profile_image ?? asset('images/default_profile.jpg') }}" alt="{{ $student->name }}" class="pwa-student-avatar">
                    <div>
                        <div class="pwa-student-name">{{ $student->name }}</div>
                        <div class="pwa-student-id">{{ $student->student_id }}</div>
                    </div>
                </div>
                <div class="pwa-attendance-buttons">
                    <button type="button" class="pwa-attendance-btn present" data-student="{{ $student->id }}" data-status="present">P</button>
                    <button type="button" class="pwa-attendance-btn absent" data-student="{{ $student->id }}" data-status="absent">A</button>
                    <button type="button" class="pwa-attendance-btn late" data-student="{{ $student->id }}" data-status="late">L</button>
                </div>
            </div>
            @endforeach
        </form>
        
        <button class="pwa-btn pwa-btn-primary" onclick="submitAttendance()">
            Submit Attendance
        </button>
    </div>
    @endif
</div>

<style>
.pwa-attendance-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: white;
    border-radius: 8px;
    margin-bottom: 8px;
}

.pwa-student-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.pwa-student-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.pwa-student-name {
    font-weight: 600;
    color: #1C1C1E;
}

.pwa-student-id {
    font-size: 12px;
    color: #6E6E73;
}

.pwa-attendance-buttons {
    display: flex;
    gap: 8px;
}

.pwa-attendance-btn {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    border: 2px solid #E5E5EA;
    background: white;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.pwa-attendance-btn.active.present {
    background: #34C759;
    color: white;
    border-color: #34C759;
}

.pwa-attendance-btn.active.absent {
    background: #FF3B30;
    color: white;
    border-color: #FF3B30;
}

.pwa-attendance-btn.active.late {
    background: #FF9500;
    color: white;
    border-color: #FF9500;
}
</style>

<script>
document.querySelectorAll('.pwa-attendance-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const studentId = this.dataset.student;
        const status = this.dataset.status;
        
        // Remove active from siblings
        this.parentElement.querySelectorAll('.pwa-attendance-btn').forEach(b => {
            b.classList.remove('active');
        });
        
        // Add active to clicked
        this.classList.add('active');
    });
});

function submitAttendance() {
    const attendance = [];
    document.querySelectorAll('.pwa-attendance-btn.active').forEach(btn => {
        attendance.push({
            student_id: btn.dataset.student,
            status: btn.dataset.status
        });
    });
    
    if (attendance.length === 0) {
        alert('Please mark attendance for at least one student');
        return;
    }
    
    // TODO: Submit to API
    console.log('Attendance:', attendance);
    alert('Attendance submitted successfully!');
}
</script>
@endsection
