@extends('pwa.layouts.app', [
    'theme' => 'teacher',
    'title' => 'My Classes',
    'headerTitle' => 'My Classes',
    'activeNav' => 'classes',
    'role' => 'teacher'
])

@section('content')
<div class="pwa-container">
    <!-- Filter Tabs -->
    <div class="pwa-tabs">
        <button class="pwa-tab active" data-filter="all">All Classes</button>
        <button class="pwa-tab" data-filter="today">Today</button>
        <button class="pwa-tab" data-filter="upcoming">Upcoming</button>
    </div>

    <!-- Classes List -->
    <div class="pwa-section">
        @forelse($classes as $class)
            @include('pwa.components.class-card', [
                'class' => $class,
                'showActions' => true
            ])
        @empty
            <div class="pwa-empty-state">
                <svg class="pwa-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <p class="pwa-empty-text">No classes found</p>
            </div>
        @endforelse
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.pwa-tab');
    const cards = document.querySelectorAll('.pwa-class-card');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            cards.forEach(card => {
                if (filter === 'all' || card.dataset.filter === filter) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
});
</script>
@endsection
