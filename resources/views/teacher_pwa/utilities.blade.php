@extends('pwa.layouts.app', [
    'theme' => 'teacher',
    'title' => 'Utilities',
    'headerTitle' => 'Utilities',
    'activeNav' => 'utilities',
    'role' => 'teacher'
])

@section('content')
<div class="pwa-container">
    <!-- Utilities Grid -->
    <div class="pwa-utilities-grid">
        <div class="pwa-utility-card" onclick="window.location.href='{{ route('teacher-pwa.timetable') }}'">
            <div class="pwa-utility-icon" style="background: #E8F5E9;">
                <svg fill="none" stroke="#2E7D32" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h3 class="pwa-utility-title">Timetable</h3>
            <p class="pwa-utility-desc">View your schedule</p>
        </div>

        <div class="pwa-utility-card" onclick="alert('Leave requests')">
            <div class="pwa-utility-icon" style="background: #E3F2FD;">
                <svg fill="none" stroke="#1976D2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="pwa-utility-title">Leave Requests</h3>
            <p class="pwa-utility-desc">Apply for leave</p>
        </div>

        <div class="pwa-utility-card" onclick="alert('Reports')">
            <div class="pwa-utility-icon" style="background: #FFF3E0;">
                <svg fill="none" stroke="#E65100" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="pwa-utility-title">Reports</h3>
            <p class="pwa-utility-desc">View & generate reports</p>
        </div>

        <div class="pwa-utility-card" onclick="alert('Exams')">
            <div class="pwa-utility-icon" style="background: #F3E5F5;">
                <svg fill="none" stroke="#6A1B9A" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
            </div>
            <h3 class="pwa-utility-title">Exams</h3>
            <p class="pwa-utility-desc">Manage exams & grades</p>
        </div>

        <div class="pwa-utility-card" onclick="alert('Curriculum')">
            <div class="pwa-utility-icon" style="background: #E0F2F1;">
                <svg fill="none" stroke="#00695C" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <h3 class="pwa-utility-title">Curriculum</h3>
            <p class="pwa-utility-desc">View curriculum</p>
        </div>

        <div class="pwa-utility-card" onclick="alert('Resources')">
            <div class="pwa-utility-icon" style="background: #FBE9E7;">
                <svg fill="none" stroke="#BF360C" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                </svg>
            </div>
            <h3 class="pwa-utility-title">Resources</h3>
            <p class="pwa-utility-desc">Teaching materials</p>
        </div>
    </div>

    <!-- Logout Section -->
    <div style="margin-top: 32px;">
        <form method="POST" action="{{ route('logout') }}" id="logout-form" onsubmit="handleLogout(event)">
            @csrf
            <button type="submit" class="pwa-utility-card" style="width: 100%; border: none; background: #FFEBEE; cursor: pointer;">
                <div class="pwa-utility-icon" style="background: #FFCDD2; margin: 0 auto 12px;">
                    <svg fill="none" stroke="#C62828" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </div>
                <h3 class="pwa-utility-title" style="color: #C62828;">Logout</h3>
                <p class="pwa-utility-desc">Sign out of your account</p>
            </button>
        </form>
    </div>
</div>

<script>
function handleLogout(event) {
    event.preventDefault();
    const form = event.target;
    
    // Submit the form
    fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(() => {
        // Force redirect to login page using replace (no back button)
        window.location.replace('{{ route("login") }}');
    }).catch(() => {
        // Even on error, redirect to login
        window.location.replace('{{ route("login") }}');
    });
    
    return false;
}
</script>

<style>
.pwa-utilities-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.pwa-utility-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: transform 0.2s;
}

.pwa-utility-card:active {
    transform: scale(0.95);
}

.pwa-utility-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
}

.pwa-utility-icon svg {
    width: 32px;
    height: 32px;
}

.pwa-utility-title {
    font-size: 16px;
    font-weight: 600;
    color: #1C1C1E;
    margin: 0 0 4px 0;
}

.pwa-utility-desc {
    font-size: 13px;
    color: #6E6E73;
    margin: 0;
}
</style>
@endsection
