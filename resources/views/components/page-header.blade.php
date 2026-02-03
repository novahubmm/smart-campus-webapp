@props([
    'icon' => 'fas fa-circle',
    'iconBg' => 'bg-blue-50 dark:bg-blue-900/30',
    'iconColor' => 'text-blue-700 dark:text-blue-200',
    'subtitle' => '',
    'subtitleColor' => 'text-gray-500 dark:text-gray-400',
    'title' => '',
    'titleColor' => 'text-gray-800 dark:text-gray-200',
])

<div class="flex items-center gap-3">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl {{ $iconBg }} {{ $iconColor }}">
        <i class="{{ $icon }}"></i>
    </span>
    <div>
        @if($subtitle)
            <p class="text-xs {{ $subtitleColor }}">{{ $subtitle }}</p>
        @endif
        <h2 class="font-semibold text-xl {{ $titleColor }} leading-tight">
            @if($title)
                {{ $title }}
            @elseif(isset($titleSlot))
                {{ $titleSlot }}
            @endif
            {{ $slot }}
        </h2>
    </div>
</div>
