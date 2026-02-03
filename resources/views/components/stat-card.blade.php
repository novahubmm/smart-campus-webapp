@props(['icon', 'title', 'number', 'subtitle'])

<div class="stat-card">
    <div class="stat-icon">
        <i class="{{ $icon }}"></i>
    </div>
    <div class="stat-content">
        <h3>{{ $title }}</h3>
        <div class="stat-number">{{ $number }}</div>
        <div class="stat-change">{{ $subtitle }}</div>
    </div>
</div>
