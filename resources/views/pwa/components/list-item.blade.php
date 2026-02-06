{{-- List Item Component --}}
@php
    $title = $title ?? 'Title';
    $subtitle = $subtitle ?? '';
    $icon = $icon ?? 'fa-circle';
    $iconBg = $iconBg ?? 'var(--info-bg)';
    $iconColor = $iconColor ?? 'var(--info)';
    $url = $url ?? '#';
    $badge = $badge ?? null;
@endphp

<a href="{{ $url }}" class="pwa-list-item">
    <div class="pwa-list-icon" style="background: {{ $iconBg }}; color: {{ $iconColor }};">
        <i class="fas {{ $icon }}"></i>
    </div>
    
    <div class="pwa-list-content">
        <div class="pwa-list-title">{{ $title }}</div>
        @if($subtitle)
            <div class="pwa-list-subtitle">{{ $subtitle }}</div>
        @endif
    </div>
    
    @if($badge)
        <div class="pwa-badge pwa-badge-{{ $badge['type'] ?? 'info' }}">
            {{ $badge['text'] }}
        </div>
    @endif
    
    <i class="pwa-list-arrow fas fa-chevron-right"></i>
</a>
