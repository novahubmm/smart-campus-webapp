@extends('pwa.layouts.app', [
    'theme' => 'guardian',
    'title' => 'Timetable',
    'headerTitle' => 'Timetable',
    'activeNav' => 'timetable',
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

    <!-- Day Tabs -->
    <div class="pwa-tabs pwa-tabs-scroll">
        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day)
            <button class="pwa-tab {{ $currentDay === $day ? 'active' : '' }}" data-day="{{ $day }}">{{ $day }}</button>
        @endforeach
    </div>

    <!-- Timetable -->
    <div class="pwa-section">
        @forelse($periods as $period)
            <div class="pwa-period-card">
                <div class="pwa-period-time">
                    <div class="pwa-period-number">Period {{ $period->period_number }}</div>
                    <div class="pwa-period-duration">{{ $period->start_time }} - {{ $period->end_time }}</div>
                </div>
                <div class="pwa-period-content">
                    <h3 class="pwa-period-subject">{{ $period->subject->name }}</h3>
                    <p class="pwa-period-teacher">{{ $period->teacher->name }}</p>
                    <p class="pwa-period-room">Room {{ $period->room->name }}</p>
                </div>
            </div>
        @empty
            <div class="pwa-empty-state">
                <svg class="pwa-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="pwa-empty-text">No classes scheduled</p>
            </div>
        @endforelse
    </div>
</div>

<style>
.pwa-tabs-scroll {
    overflow-x: auto;
    white-space: nowrap;
    -webkit-overflow-scrolling: touch;
}

.pwa-period-card {
    display: flex;
    gap: 16px;
    padding: 16px;
    background: white;
    border-radius: 12px;
    margin-bottom: 12px;
}

.pwa-period-time {
    flex-shrink: 0;
    text-align: center;
    padding: 12px;
    background: #F7F9FC;
    border-radius: 8px;
}

.pwa-period-number {
    font-size: 14px;
    font-weight: 600;
    color: #26BFFF;
    margin-bottom: 4px;
}

.pwa-period-duration {
    font-size: 12px;
    color: #6E6E73;
}

.pwa-period-content {
    flex: 1;
}

.pwa-period-subject {
    font-size: 16px;
    font-weight: 600;
    color: #1C1C1E;
    margin: 0 0 4px 0;
}

.pwa-period-teacher {
    font-size: 14px;
    color: #6E6E73;
    margin: 0 0 4px 0;
}

.pwa-period-room {
    font-size: 13px;
    color: #26BFFF;
    font-weight: 600;
    margin: 0;
}
</style>

<script>
document.getElementById('student-selector').addEventListener('change', function() {
    window.location.href = '{{ route("guardian-pwa.timetable") }}?student=' + this.value;
});

document.querySelectorAll('[data-day]').forEach(tab => {
    tab.addEventListener('click', function() {
        const day = this.dataset.day;
        window.location.href = '{{ route("guardian-pwa.timetable") }}?day=' + day;
    });
});
</script>
@endsection
