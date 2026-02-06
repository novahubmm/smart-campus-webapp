@extends('pwa.layouts.app', [
    'theme' => 'guardian',
    'title' => 'Attendance',
    'headerTitle' => 'Attendance',
    'activeNav' => 'attendance',
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

    <!-- Attendance Summary -->
    <div class="pwa-stats-grid">
        @include('pwa.components.stat-card', [
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
            'value' => $selectedStudent->attendance_rate . '%',
            'label' => 'Attendance Rate',
            'color' => '#34C759'
        ])
        @include('pwa.components.stat-card', [
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
            'value' => $selectedStudent->present_days,
            'label' => 'Present Days',
            'color' => '#26BFFF'
        ])
        @include('pwa.components.stat-card', [
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>',
            'value' => $selectedStudent->absent_days,
            'label' => 'Absent Days',
            'color' => '#FF3B30'
        ])
        @include('pwa.components.stat-card', [
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
            'value' => $selectedStudent->late_days,
            'label' => 'Late Days',
            'color' => '#FF9500'
        ])
    </div>

    <!-- Monthly Calendar -->
    <div class="pwa-card">
        <h3 class="pwa-card-title">This Month</h3>
        <div class="pwa-calendar">
            @foreach($calendar as $week)
                <div class="pwa-calendar-week">
                    @foreach($week as $day)
                        <div class="pwa-calendar-day {{ $day['status'] }}">
                            <span class="pwa-calendar-date">{{ $day['date'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
        <div class="pwa-calendar-legend">
            <div class="pwa-legend-item">
                <span class="pwa-legend-dot present"></span>
                <span>Present</span>
            </div>
            <div class="pwa-legend-item">
                <span class="pwa-legend-dot absent"></span>
                <span>Absent</span>
            </div>
            <div class="pwa-legend-item">
                <span class="pwa-legend-dot late"></span>
                <span>Late</span>
            </div>
        </div>
    </div>

    <!-- Recent Attendance -->
    <div class="pwa-section">
        <h3 class="pwa-section-title">Recent Attendance</h3>
        @foreach($recentAttendance as $record)
            <div class="pwa-list-item">
                <div class="pwa-list-content">
                    <div class="pwa-list-title">{{ $record->date->format('M d, Y') }}</div>
                    <div class="pwa-list-subtitle">{{ $record->date->format('l') }}</div>
                </div>
                <span class="pwa-attendance-badge {{ $record->status }}">{{ ucfirst($record->status) }}</span>
            </div>
        @endforeach
    </div>
</div>

<style>
.pwa-calendar {
    margin-top: 16px;
}

.pwa-calendar-week {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
    margin-bottom: 8px;
}

.pwa-calendar-day {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: #F7F9FC;
    font-size: 14px;
    font-weight: 600;
}

.pwa-calendar-day.present {
    background: #E8F5E9;
    color: #2E7D32;
}

.pwa-calendar-day.absent {
    background: #FFEBEE;
    color: #C62828;
}

.pwa-calendar-day.late {
    background: #FFF3E0;
    color: #E65100;
}

.pwa-calendar-legend {
    display: flex;
    gap: 16px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #E5E5EA;
}

.pwa-legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #6E6E73;
}

.pwa-legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.pwa-legend-dot.present {
    background: #34C759;
}

.pwa-legend-dot.absent {
    background: #FF3B30;
}

.pwa-legend-dot.late {
    background: #FF9500;
}

.pwa-attendance-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.pwa-attendance-badge.present {
    background: #E8F5E9;
    color: #2E7D32;
}

.pwa-attendance-badge.absent {
    background: #FFEBEE;
    color: #C62828;
}

.pwa-attendance-badge.late {
    background: #FFF3E0;
    color: #E65100;
}
</style>

<script>
document.getElementById('student-selector').addEventListener('change', function() {
    window.location.href = '{{ route("guardian-pwa.attendance") }}?student=' + this.value;
});
</script>
@endsection
