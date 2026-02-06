@extends('pwa.layouts.app', [
    'theme' => 'guardian',
    'title' => 'Guardian Home',
    'headerTitle' => 'Home',
    'activeNav' => 'home',
    'showNotifications' => true,
    'unreadCount' => $unreadCount ?? 0,
    'themeColor' => '#26BFFF',
    'role' => 'guardian'
])

@section('content')
<div x-data="guardianHome()">
    {{-- Welcome Card --}}
    <div class="welcome-card">
        <p class="welcome-greeting">Welcome back,</p>
        <h2 class="welcome-name">{{ $guardian->name ?? 'Parent' }}</h2>
        <p class="welcome-subtitle">
            <i class="fas fa-child"></i> {{ count($students ?? []) }} {{ Str::plural('Child', count($students ?? [])) }}
        </p>
    </div>

    {{-- Children List --}}
    <div class="section-header">
        <h2 class="section-title">My Children</h2>
    </div>

    @forelse($students ?? [] as $student)
        <div class="pwa-card" onclick="window.location='{{ route('guardian-pwa.student-detail', $student->id) }}'">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #26BFFF, #17a4e1); display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700;">
                    {{ strtoupper(substr($student->name, 0, 1)) }}
                </div>
                
                <div style="flex: 1;">
                    <h3 style="margin: 0 0 4px 0; font-size: 18px; font-weight: 600; color: var(--text-primary);">
                        {{ $student->name }}
                    </h3>
                    <p style="margin: 0; font-size: 14px; color: var(--text-secondary);">
                        <i class="fas fa-graduation-cap"></i> {{ $student->grade }} - {{ $student->section }}
                    </p>
                </div>
                
                <i class="fas fa-chevron-right" style="color: var(--text-muted);"></i>
            </div>
            
            {{-- Quick Stats --}}
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border);">
                <div style="text-align: center;">
                    <div style="font-size: 20px; font-weight: 700; color: var(--success);">
                        {{ $student->attendance_rate ?? '0' }}%
                    </div>
                    <div style="font-size: 11px; color: var(--text-secondary);">Attendance</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 20px; font-weight: 700; color: var(--info);">
                        {{ $student->homework_pending ?? '0' }}
                    </div>
                    <div style="font-size: 11px; color: var(--text-secondary);">Homework</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 20px; font-weight: 700; color: var(--warning);">
                        {{ $student->fees_pending ?? '0' }}
                    </div>
                    <div style="font-size: 11px; color: var(--text-secondary);">Fees Due</div>
                </div>
            </div>
        </div>
    @empty
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-child"></i>
            </div>
            <h3 class="empty-state-title">No Children Found</h3>
            <p class="empty-state-subtitle">No students are linked to your account.</p>
        </div>
    @endforelse

    {{-- Quick Actions --}}
    <div class="section-header">
        <h2 class="section-title">Quick Actions</h2>
    </div>

    <div style="display: grid; gap: 8px;">
        @include('pwa.components.list-item', [
            'title' => 'Attendance',
            'subtitle' => 'View attendance records',
            'icon' => 'fa-calendar-check',
            'iconBg' => '#E3F2FD',
            'iconColor' => '#2196F3',
            'url' => route('guardian-pwa.attendance')
        ])
        
        @include('pwa.components.list-item', [
            'title' => 'Homework',
            'subtitle' => 'Check homework assignments',
            'icon' => 'fa-book',
            'iconBg' => '#FFF8E1',
            'iconColor' => '#FFC107',
            'url' => route('guardian-pwa.homework')
        ])
        
        @include('pwa.components.list-item', [
            'title' => 'Timetable',
            'subtitle' => 'View class schedule',
            'icon' => 'fa-calendar-alt',
            'iconBg' => '#E8F5E9',
            'iconColor' => '#4CAF50',
            'url' => route('guardian-pwa.timetable')
        ])
        
        @include('pwa.components.list-item', [
            'title' => 'School Fees',
            'subtitle' => 'View payment information',
            'icon' => 'fa-money-bill-wave',
            'iconBg' => '#FFF3E0',
            'iconColor' => '#FF9800',
            'url' => route('guardian-pwa.fees')
        ])
        
        @include('pwa.components.list-item', [
            'title' => 'Announcements',
            'subtitle' => 'School news and updates',
            'icon' => 'fa-bullhorn',
            'iconBg' => '#F3E5F5',
            'iconColor' => '#9C27B0',
            'url' => route('guardian-pwa.announcements')
        ])
    </div>

    {{-- Recent Announcements --}}
    @if(isset($recentAnnouncements) && count($recentAnnouncements) > 0)
        <div class="section-header">
            <h2 class="section-title">Recent Announcements</h2>
            <a href="{{ route('guardian-pwa.announcements') }}" class="section-action">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        @foreach($recentAnnouncements as $announcement)
            <div class="pwa-card" onclick="window.location='{{ route('guardian-pwa.announcement-detail', $announcement->id) }}'">
                <div style="display: flex; align-items: start; gap: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 8px; background: var(--info-bg); display: flex; align-items: center; justify-content: center; color: var(--info); flex-shrink: 0;">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div style="flex: 1;">
                        <h4 style="margin: 0 0 4px 0; font-size: 16px; font-weight: 600;">
                            {{ $announcement->title }}
                        </h4>
                        <p style="margin: 0; font-size: 14px; color: var(--text-secondary); line-height: 1.5;">
                            {{ Str::limit($announcement->content, 100) }}
                        </p>
                        <div style="margin-top: 8px; font-size: 12px; color: var(--text-muted);">
                            <i class="fas fa-clock"></i> {{ $announcement->date }}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection

@push('scripts')
<script>
function guardianHome() {
    return {
        init() {
            console.log('Guardian home initialized');
        }
    }
}
</script>
@endpush
