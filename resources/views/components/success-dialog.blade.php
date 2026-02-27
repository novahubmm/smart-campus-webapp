<div
    x-data="{
        open: false,
        title: '',
        message: '',
        confirmText: '',
        onConfirm: null,
        openDialog(detail = {}) {
            this.title = detail.title || '{{ __('components.Success') }}';
            this.message = detail.message || '{{ __('components.Operation completed successfully') }}';
            this.confirmText = detail.confirmText || '{{ __('components.OK') }}';
            this.onConfirm = detail.onConfirm || null;
            this.open = true;
        },
        close() {
            this.open = false;
            this.onConfirm = null;
        },
        confirm() {
            if (typeof this.onConfirm === 'function') {
                this.onConfirm();
            }
            this.close();
        }
    }"
    @success-show.window="openDialog($event.detail)"
    x-cloak
>
    <div
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
        aria-modal="true"
        role="dialog"
    >
        <div class="fixed inset-0 bg-gray-900/60 dark:bg-black/70" @click="close()" aria-hidden="true"></div>

        <div
            x-show="open"
            x-transition
            class="relative w-full max-w-md bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl overflow-hidden"
        >
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                <span class="w-10 h-10 rounded-full bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-200 flex items-center justify-center">
                    <i class="fas fa-check-circle"></i>
                </span>
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white" x-text="title"></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="message"></p>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/40 flex justify-end gap-3">
                <button
                    type="button"
                    class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-green-600 hover:bg-green-700 focus:ring-2 focus:ring-offset-2 focus:ring-green-500 focus:ring-offset-white dark:focus:ring-offset-gray-900 transition-colors"
                    @click="confirm()"
                >
                    <span x-text="confirmText"></span>
                </button>
            </div>
        </div>
    </div>
</div>
