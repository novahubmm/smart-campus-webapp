@extends('pwa.layouts.app', [
    'theme' => 'teacher',
    'title' => 'Timetable',
    'headerTitle' => $headerTitle ?? 'Timetable',
    'activeNav' => $activeNav ?? 'timetable',
    'role' => 'teacher'
])

@section('content')
<div class="pwa-container">
    {{-- Day Tabs --}}
    <div class="pwa-tabs" style="overflow-x: auto; white-space: nowrap; -webkit-overflow-scrolling: touch;">
        @foreach($timetable as $dayKey => $dayData)
            <button 
                class="pwa-tab {{ $dayData['is_today'] ? 'active' : '' }}" 
                data-day="{{ $dayKey }}"
                onclick="showDay('{{ $dayKey }}')"
            >
                {{ $dayData['day'] }}
                @if($dayData['is_today'])
                    <span style="display: inline-block; width: 6px; height: 6px; background: var(--primary); border-radius: 50%; margin-left: 4px;"></span>
                @endif
            </button>
        @endforeach
    </div>

    {{-- Timetable Content --}}
    @foreach($timetable as $dayKey => $dayData)
        <div 
            class="day-content" 
            id="day-{{ $dayKey }}" 
            style="display: {{ $dayData['is_today'] ? 'block' : 'none' }};"
        >
            @if(count($dayData['periods']) > 0)
                <div class="pwa-section">
                    @foreach($dayData['periods'] as $period)
                        @if($period['is_break'])
                            {{-- Break Period --}}
                            <div class="pwa-card" style="background: var(--bg-secondary); border-left: 4px solid var(--text-muted);">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 40px; height: 40px; border-radius: 8px; background: var(--bg-tertiary); display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
                                        <i class="fas fa-coffee"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <h4 style="margin: 0; font-size: 16px; font-weight: 600; color: var(--text-secondary);">
                                            Break Time
                                        </h4>
                                        <p style="margin: 4px 0 0 0; font-size: 14px; color: var(--text-muted);">
                                            <i class="fas fa-clock"></i> {{ $period['time'] }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Regular Period --}}
                            <div class="pwa-card" style="border-left: 4px solid var(--primary);">
                                <div style="display: flex; align-items: start; gap: 12px;">
                                    <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; font-weight: 700; flex-shrink: 0;">
                                        {{ $period['period_number'] }}
                                    </div>
                                    <div style="flex: 1;">
                                        <h3 style="margin: 0 0 4px 0; font-size: 18px; font-weight: 600; color: var(--text-primary);">
                                            {{ $period['subject'] }}
                                        </h3>
                                        <p style="margin: 0 0 8px 0; font-size: 14px; color: var(--text-secondary);">
                                            <i class="fas fa-users"></i> {{ $period['class'] }}
                                            @if($period['room'] !== 'N/A')
                                                • <i class="fas fa-door-open"></i> {{ $period['room'] }}
                                            @endif
                                        </p>
                                        <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: var(--info-bg); color: var(--info); border-radius: 12px; font-size: 13px; font-weight: 500;">
                                            <i class="fas fa-clock"></i>
                                            {{ $period['time'] }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                {{-- No Classes --}}
                <div class="pwa-empty-state">
                    <svg class="pwa-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="pwa-empty-text">No classes scheduled for {{ $dayData['day'] }}</p>
                </div>
            @endif
        </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
function showDay(day) {
    // Hide all day contents
    document.querySelectorAll('.day-content').forEach(content => {
        content.style.display = 'none';
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.pwa-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected day content
    document.getElementById('day-' + day).style.display = 'block';
    
    // Add active class to selected tab
    document.querySelector('[data-day="' + day + '"]').classList.add('active');
}
</script>
@endpush
