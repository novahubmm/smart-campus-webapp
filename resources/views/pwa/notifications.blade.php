@extends('pwa.layouts.app', [
    'theme' => 'guardian',
    'title' => 'Notifications',
    'headerTitle' => 'Notifications',
    'showBack' => true,
    'hideBottomNav' => true,
    'role' => 'guardian'
])

@section('content')
<div class="pwa-container">
    <div class="pwa-section">
        <div class="pwa-empty-state">
            <svg class="pwa-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <p class="pwa-empty-text">No notifications yet</p>
        </div>
    </div>
</div>
@endsection
