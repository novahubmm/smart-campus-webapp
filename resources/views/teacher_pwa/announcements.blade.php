@extends('pwa.layouts.app', [
    'theme' => 'teacher',
    'title' => 'Announcements',
    'headerTitle' => 'Announcements',
    'activeNav' => 'announcements',
    'role' => 'teacher'
])

@section('content')
<div class="pwa-container">
    <!-- Announcements List -->
    <div class="pwa-section">
        @forelse($announcements as $announcement)
            <div class="pwa-card" onclick="viewAnnouncement({{ $announcement->id }})">
                <div class="pwa-announcement-header">
                    <span class="pwa-badge pwa-badge-{{ $announcement->priority }}">{{ ucfirst($announcement->priority) }}</span>
                    <span class="pwa-announcement-date">{{ $announcement->created_at->diffForHumans() }}</span>
                </div>
                <h3 class="pwa-announcement-title">{{ $announcement->title }}</h3>
                <p class="pwa-announcement-excerpt">{{ Str::limit($announcement->content, 100) }}</p>
                <div class="pwa-announcement-footer">
                    <span class="pwa-announcement-author">By {{ $announcement->author->name }}</span>
                </div>
            </div>
        @empty
            <div class="pwa-empty-state">
                <svg class="pwa-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                </svg>
                <p class="pwa-empty-text">No announcements</p>
            </div>
        @endforelse
    </div>
</div>

<style>
.pwa-announcement-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.pwa-announcement-date {
    font-size: 12px;
    color: #6E6E73;
}

.pwa-announcement-title {
    font-size: 16px;
    font-weight: 600;
    color: #1C1C1E;
    margin: 0 0 8px 0;
}

.pwa-announcement-excerpt {
    font-size: 14px;
    color: #6E6E73;
    line-height: 1.5;
    margin: 0 0 12px 0;
}

.pwa-announcement-footer {
    padding-top: 12px;
    border-top: 1px solid #E5E5EA;
}

.pwa-announcement-author {
    font-size: 13px;
    color: #6E6E73;
}

.pwa-badge-high {
    background: #FFEBEE;
    color: #C62828;
}

.pwa-badge-normal {
    background: #E3F2FD;
    color: #1976D2;
}

.pwa-badge-low {
    background: #F5F5F5;
    color: #616161;
}
</style>

<script>
function viewAnnouncement(id) {
    alert('View announcement ' + id);
}
</script>
@endsection
