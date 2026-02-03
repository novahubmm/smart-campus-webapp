<div
    x-data="{
        open: false,
        title: '',
        message: '',
        confirmText: '',
        cancelText: '',
        onConfirm: null,
        openDialog(detail = {}) {
            this.title = detail.title || '{{ __('components.Confirm action') }}';
            this.message = detail.message || '{{ __('components.Are you sure you want to continue?') }}';
            this.confirmText = detail.confirmText || '{{ __('components.Confirm') }}';
            this.cancelText = detail.cancelText || '{{ __('components.Cancel') }}';
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
    @confirm-show.window="openDialog($event.detail)"
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
                <span class="w-10 h-10 rounded-full bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-200 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle"></i>
                </span>
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white" x-text="title"></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="message"></p>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/40 flex justify-end gap-3">
                <button
                    type="button"
                    class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    @click="close()"
                    x-text="cancelText"
                ></button>
                <button
                    type="button"
                    class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700 focus:ring-2 focus:ring-offset-2 focus:ring-red-500 focus:ring-offset-white dark:focus:ring-offset-gray-900 transition-colors"
                    @click="confirm()"
                >
                    <span x-text="confirmText"></span>
                </button>
            </div>
        </div>
    </div>
</div>
