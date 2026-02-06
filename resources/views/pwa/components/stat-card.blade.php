{{-- Statistics Card Component --}}
@php
    $value = $value ?? '0';
    $label = $label ?? 'Stat';
    $icon = $icon ?? 'fa-chart-line';
    $color = $color ?? 'var(--primary-color)';
    $bgColor = $bgColor ?? 'var(--info-bg)';
@endphp

<div class="stat-card" style="background: {{ $bgColor }};">
    <div style="font-size: 32px; color: {{ $color }}; margin-bottom: 8px;">
        <i class="fas {{ $icon }}"></i>
    </div>
    <div class="stat-card-value" style="color: {{ $color }};">{{ $value }}</div>
    <div class="stat-card-label">{{ $label }}</div>
</div>
