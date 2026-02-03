@props([
    'id' => 'formModal',
    'title' => 'Form',
    'icon' => 'fas fa-plus',
    'action' => '#',
    'method' => 'POST',
    'submitText' => 'Save',
    'cancelText' => 'Cancel',
])

<div id="{{ $id }}" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeModal('{{ $id }}')"></div>
    
    <!-- Modal Content -->
    <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-md shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden" onclick="event.stopPropagation()">
        <form action="{{ $action }}" method="POST">
            @csrf
            @if($method !== 'POST')
                @method($method)
            @endif
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                        <i class="{{ $icon }}"></i>
                    </span>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                </div>
                <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors" onclick="closeModal('{{ $id }}')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-5 space-y-4 max-h-[60vh] overflow-y-auto">
                {{ $slot }}
            </div>
            
            <!-- Modal Footer -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                <button type="button" class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" onclick="closeModal('{{ $id }}')">
                    {{ $cancelText }}
                </button>
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                    <i class="fas fa-check"></i>{{ $submitText }}
                </button>
            </div>
        </form>
    </div>
</div>

@once
<script>
    // Helper functions for opening/closing modals
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    };
    
    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    };
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('[id$="Modal"]');
            modals.forEach(modal => {
                if (modal.style.display === 'flex') {
                    closeModal(modal.id);
                }
            });
        }
    });
</script>
@endonce
