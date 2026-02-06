@extends('pwa.layouts.app', [
    'theme' => 'guardian',
    'title' => 'Profile',
    'headerTitle' => 'Profile',
    'activeNav' => 'profile',
    'role' => 'guardian'
])

@section('content')
<div class="pwa-container">
    <!-- Profile Header -->
    <div class="pwa-profile-header">
        <img src="{{ $guardian->avatar ?? asset('images/default_profile.jpg') }}" alt="{{ $guardian->name }}" class="pwa-profile-photo">
        <h2 class="pwa-profile-name">{{ $guardian->name }}</h2>
        <p class="pwa-profile-role">Guardian</p>
        <p class="pwa-profile-occupation">{{ $guardian->occupation }}</p>
    </div>

    <!-- Role Switcher (for multi-role users) -->
    @if(count($availableRoles) > 1)
    <div class="pwa-card">
        <h3 class="pwa-card-title">Switch Role</h3>
        <div class="pwa-role-switcher">
            @foreach($availableRoles as $role)
                <button class="pwa-role-btn {{ $currentRole === $role ? 'active' : '' }}" onclick="switchRole('{{ $role }}')">
                    <svg class="pwa-role-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($role === 'teacher')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        @endif
                    </svg>
                    <span>{{ ucfirst($role) }}</span>
                </button>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Profile Info -->
    <div class="pwa-card">
        <h3 class="pwa-card-title">Personal Information</h3>
        <div class="pwa-info-list">
            <div class="pwa-info-item">
                <span class="pwa-info-label">Email</span>
                <span class="pwa-info-value">{{ $guardian->email }}</span>
            </div>
            <div class="pwa-info-item">
                <span class="pwa-info-label">Phone</span>
                <span class="pwa-info-value">{{ $guardian->phone }}</span>
            </div>
            <div class="pwa-info-item">
                <span class="pwa-info-label">Occupation</span>
                <span class="pwa-info-value">{{ $guardian->occupation }}</span>
            </div>
            <div class="pwa-info-item">
                <span class="pwa-info-label">Address</span>
                <span class="pwa-info-value">{{ $guardian->address }}</span>
            </div>
        </div>
    </div>

    <!-- Children -->
    <div class="pwa-card">
        <h3 class="pwa-card-title">My Children</h3>
        <div class="pwa-children-list">
            @foreach($students as $student)
                <div class="pwa-child-item" onclick="window.location.href='{{ route('guardian-pwa.student-detail', $student->id) }}'">
                    <img src="{{ $student->profile_image ?? asset('images/student_default_profile.jpg') }}" alt="{{ $student->name }}" class="pwa-child-photo">
                    <div class="pwa-child-info">
                        <div class="pwa-child-name">{{ $student->name }}</div>
                        <div class="pwa-child-class">{{ $student->grade }} • {{ $student->section }}</div>
                    </div>
                    <svg class="pwa-list-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Settings -->
    <div class="pwa-section">
        <h3 class="pwa-section-title">Settings</h3>
        
        <div class="pwa-list-item" onclick="alert('Edit profile')">
            <div class="pwa-list-content">
                <div class="pwa-list-title">Edit Profile</div>
            </div>
            <svg class="pwa-list-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </div>

        <div class="pwa-list-item" onclick="alert('Change password')">
            <div class="pwa-list-content">
                <div class="pwa-list-title">Change Password</div>
            </div>
            <svg class="pwa-list-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </div>

        <div class="pwa-list-item" onclick="alert('Notifications')">
            <div class="pwa-list-content">
                <div class="pwa-list-title">Notifications</div>
            </div>
            <svg class="pwa-list-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </div>

        <div class="pwa-list-item" onclick="alert('Language')">
            <div class="pwa-list-content">
                <div class="pwa-list-title">Language</div>
                <div class="pwa-list-subtitle">English</div>
            </div>
            <svg class="pwa-list-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </div>

        <div class="pwa-list-item" onclick="logout()">
            <div class="pwa-list-content">
                <div class="pwa-list-title" style="color: #FF3B30;">Logout</div>
            </div>
            <svg class="pwa-list-icon" fill="none" stroke="#FF3B30" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
        </div>
    </div>
</div>

<style>
.pwa-profile-header {
    text-align: center;
    padding: 24px;
    background: white;
    border-radius: 16px;
    margin-bottom: 16px;
}

.pwa-profile-photo {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 16px;
}

.pwa-profile-name {
    font-size: 24px;
    font-weight: 700;
    color: #1C1C1E;
    margin: 0 0 4px 0;
}

.pwa-profile-role {
    font-size: 14px;
    color: #6E6E73;
    margin: 0 0 4px 0;
}

.pwa-profile-occupation {
    font-size: 14px;
    color: #26BFFF;
    font-weight: 600;
    margin: 0;
}

.pwa-role-switcher {
    display: flex;
    gap: 12px;
}

.pwa-role-btn {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 16px;
    background: #F7F9FC;
    border: 2px solid #E5E5EA;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.pwa-role-btn.active {
    background: #26BFFF;
    border-color: #26BFFF;
    color: white;
}

.pwa-role-icon {
    width: 32px;
    height: 32px;
}

.pwa-info-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.pwa-info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.pwa-info-label {
    font-size: 14px;
    color: #6E6E73;
}

.pwa-info-value {
    font-size: 14px;
    font-weight: 600;
    color: #1C1C1E;
    text-align: right;
}

.pwa-children-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 16px;
}

.pwa-child-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #F7F9FC;
    border-radius: 12px;
    cursor: pointer;
}

.pwa-child-photo {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
}

.pwa-child-info {
    flex: 1;
}

.pwa-child-name {
    font-size: 15px;
    font-weight: 600;
    color: #1C1C1E;
    margin-bottom: 2px;
}

.pwa-child-class {
    font-size: 13px;
    color: #6E6E73;
}
</style>

<script>
function switchRole(role) {
    if (confirm('Switch to ' + role + ' role?')) {
        // TODO: Call API to switch role
        if (role === 'teacher') {
            window.location.href = '{{ route("teacher-pwa.dashboard") }}';
        } else {
            window.location.href = '{{ route("guardian-pwa.home") }}';
        }
    }
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("logout") }}';
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        
        // Submit and redirect
        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(() => {
            window.location.replace('{{ route("login") }}');
        }).catch(() => {
            window.location.replace('{{ route("login") }}');
        });
    }
}
</script>
@endsection
