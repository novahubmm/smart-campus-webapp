@props([
    'icon' => 'fas fa-circle',
    'iconBg' => 'bg-blue-50 dark:bg-blue-900/30',
    'iconColor' => 'text-blue-600 dark:text-blue-400',
    'title' => '',
    'subtitle' => '',
    'badge' => null,
    'badgeColor' => 'active',
    'editRoute' => null,
    'editText' => 'Edit',
    'deleteRoute' => null,
    'deleteText' => 'Delete',
    'deleteTitle' => 'Confirm Delete',
    'deleteMessage' => 'Are you sure you want to delete this item? This action cannot be undone.',
])

<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-6">
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
        <!-- Header Info -->
        <div class="flex items-start gap-4">
            <!-- Icon -->
            <div class="flex-shrink-0">
                <div class="w-14 h-14 rounded-xl {{ $iconBg }} {{ $iconColor }} flex items-center justify-center text-xl">
                    <i class="{{ $icon }}"></i>
                </div>
            </div>
            
            <!-- Title & Meta -->
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ $title }}</h1>
                <div class="flex flex-wrap items-center gap-3">
                    @if($badge)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                            @if($badgeColor === 'active') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100
                            @elseif($badgeColor === 'inactive') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                            @elseif($badgeColor === 'pending') bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-100
                            @elseif($badgeColor === 'completed') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-100
                            @elseif($badgeColor === 'info') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-100
                            @elseif($badgeColor === 'warning') bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-100
                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                            @endif">
                            {{ $badge }}
                        </span>
                    @endif
                    
                    @if($subtitle)
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $subtitle }}</span>
                    @endif
                    
                    {{ $meta ?? '' }}
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-2 lg:flex-shrink-0">
            @if($editRoute)
                <a href="{{ $editRoute }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
                    <i class="fas fa-edit"></i>
                    <span>{{ $editText }}</span>
                </a>
            @endif

            {{ $actions ?? '' }}
            
            @if($deleteRoute)
                <form method="POST" action="{{ $deleteRoute }}" class="inline-block"
                      @submit.prevent="$dispatch('confirm-show', {
                          title: '{{ $deleteTitle }}',
                          message: '{{ $deleteMessage }}',
                          confirmText: '{{ __('components.Delete') }}',
                          cancelText: '{{ __('components.Cancel') }}',
                          onConfirm: () => $el.submit()
                      })">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors">
                        <i class="fas fa-trash"></i>
                        <span>{{ $deleteText }}</span>
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
