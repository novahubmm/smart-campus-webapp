{{-- Bottom Navigation Component --}}
@php
    $role = $role ?? 'guardian'; // teacher or guardian
    $active = $active ?? 'home';
@endphp

<nav class="pwa-bottom-nav">
    <div class="bottom-nav-container">
        @if($role === 'teacher')
            {{-- Teacher Navigation --}}
            <a href="{{ route('teacher-pwa.dashboard') }}" 
               class="bottom-nav-item {{ $active === 'home' ? 'active' : '' }}">
                <i class="bottom-nav-icon fas fa-home"></i>
                <span class="bottom-nav-label">Home</span>
            </a>
            
            <a href="{{ route('teacher-pwa.classes') }}" 
               class="bottom-nav-item {{ $active === 'classes' ? 'active' : '' }}">
                <i class="bottom-nav-icon fas fa-chalkboard"></i>
                <span class="bottom-nav-label">Classes</span>
            </a>
            
            <a href="{{ route('teacher-pwa.attendance') }}" 
               class="bottom-nav-item {{ $active === 'attendance' ? 'active' : '' }}">
                <i class="bottom-nav-icon fas fa-clipboard-check"></i>
                <span class="bottom-nav-label">Attendance</span>
            </a>
            
            <a href="{{ route('teacher-pwa.utilities') }}" 
               class="bottom-nav-item {{ $active === 'utilities' ? 'active' : '' }}">
                <i class="bottom-nav-icon fas fa-th"></i>
                <span class="bottom-nav-label">More</span>
            </a>
            
            <a href="{{ route('teacher-pwa.profile') }}" 
               class="bottom-nav-item {{ $active === 'profile' ? 'active' : '' }}">
                <i class="bottom-nav-icon fas fa-user"></i>
                <span class="bottom-nav-label">Profile</span>
            </a>
        @else
            {{-- Guardian Navigation --}}
            <a href="{{ route('guardian-pwa.home') }}" 
               class="bottom-nav-item {{ $active === 'home' ? 'active' : '' }}">
                <i class="bottom-nav-icon fas fa-home"></i>
                <span class="bottom-nav-label">Home</span>
            </a>
            
            <a href="{{ route('guardian-pwa.attendance') }}" 
               class="bottom-nav-item {{ $active === 'attendance' ? 'active' : '' }}">
                <i class="bottom-nav-icon fas fa-calendar-check"></i>
                <span class="bottom-nav-label">Attendance</span>
            </a>
            
            <a href="{{ route('guardian-pwa.homework') }}" 
               class="bottom-nav-item {{ $active === 'homework' ? 'active' : '' }}">
                <i class="bottom-nav-icon fas fa-book"></i>
                <span class="bottom-nav-label">Homework</span>
            </a>
            
            <a href="{{ route('guardian-pwa.utilities') }}" 
               class="bottom-nav-item {{ $active === 'utilities' ? 'active' : '' }}">
                <i class="bottom-nav-icon fas fa-th"></i>
                <span class="bottom-nav-label">More</span>
            </a>
            
            <a href="{{ route('guardian-pwa.profile') }}" 
               class="bottom-nav-item {{ $active === 'profile' ? 'active' : '' }}">
                <i class="bottom-nav-icon fas fa-user"></i>
                <span class="bottom-nav-label">Profile</span>
            </a>
        @endif
    </div>
</nav>
