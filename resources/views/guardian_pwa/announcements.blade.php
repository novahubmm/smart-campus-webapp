@extends('pwa.layouts.app', [
    'theme' => 'guardian',
    'title' => 'Announcements',
    'headerTitle' => 'Announcements',
    'activeNav' => 'announcements',
    'role' => 'guardian'
])

@section('content')
<div class="pwa-container">
    <!-- Filter Tabs -->
    <div class="pwa-tabs">
        <button class="pwa-tab active" data-filter="all">All</button>
        <button class="pwa-tab" data-filter="school">School</button>
        <button class="pwa-tab" data-filter="class">Class</button>
        <button class="pwa-tab" data-filter="urgent">Urgent</button>
    </div>

    <!-- Announcements List -->
    <div class="pwa-section">
        @forelse($announcements as $announcement)
            <div class="pwa-card" data-filter="{{ $announcement->category }}" onclick="window.location.href='{{ route('guardian-pwa.announcement-detail', $announcement->id) }}'">
                <div class="pwa-announcement-header">
                    <span class="pwa-badge pwa-badge-{{ $announcement->priority }}">{{ ucfirst($announcement->priority) }}</span>
                    <span class="pwa-announcement-date">{{ $announcement->created_at->diffForHumans() }}</span>
                </div>
                <h3 class="pwa-announcement-title">{{ $announcement->title }}</h3>
                <p class="pwa-announcement-excerpt">{{ Str::limit($announcement->content, 100) }}</p>
                <div class="pwa-announcement-footer">
                    <div class="pwa-announcement-meta">
                        <svg class="pwa-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span>{{ $announcement->author->name }}</span>
                    </div>
                    @if($announcement->attachments_count > 0)
                        <div class="pwa-announcement-meta">
                            <svg class="pwa-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                            </svg>
                            <span>{{ $announcement->attachments_count }} attachment(s)</span>
                        </div>
                    @endif
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
    display: flex;
    gap: 16px;
    padding-top: 12px;
    border-top: 1px solid #E5E5EA;
}

.pwa-announcement-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #6E6E73;
}

.pwa-badge-urgent {
    background: #FFEBEE;
    color: #C62828;
}

.pwa-badge-high {
    background: #FFF3E0;
    color: #E65100;
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
document.querySelectorAll('.pwa-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.pwa-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.dataset.filter;
        document.querySelectorAll('[data-filter]').forEach(card => {
            if (filter === 'all' || card.dataset.filter === filter) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
});
</script>
@endsection
