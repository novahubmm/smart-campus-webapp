@props([
    'routeName' => '',
    'routePattern' => '',
    'label' => '',
    'icon' => '',
    'disabled' => false,
])

@php
    try {
        $href = $routeName ? route($routeName) : '#';
        $active = $routePattern ? (bool) request()->routeIs($routePattern) : false;
    } catch (\Exception $e) {
        $href = '#';
        $active = false;
        $disabled = true;
    }
    
    // Ensure label is always a string
    $labelText = is_array($label) ? (string) ($label[0] ?? '') : (string) $label;
    $iconClass = is_array($icon) ? (string) ($icon[0] ?? '') : (string) $icon;
    
    $stateClasses = $active
        ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-200 ring-1 ring-inset ring-blue-500/30'
        : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800';
    $base = 'relative group flex items-center px-3 py-2 gap-3 text-sm font-semibold rounded-xl transition-all duration-150';
@endphp

<a
    @if(!$disabled) href="{{ $href }}" @endif
    @if($disabled) aria-disabled="true" role="link" tabindex="-1" @endif
    class="{{ $base }} {{ $stateClasses }} {{ $disabled ? 'opacity-60 cursor-not-allowed' : '' }}">
    @if($iconClass)
        <span class="flex items-center justify-center w-9 h-9 flex-shrink-0 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-300 group-hover:bg-white/70 dark:group-hover:bg-gray-700 transition-colors">
            <i class="{{ $iconClass }} text-base"></i>
        </span>
    @endif
    <span class="truncate">{{ $labelText }}</span>
</a>