<div
    x-data="{
        open: false,
        title: '',
        message: '',
        type: 'info',
        okText: '',
        onOk: null,
        openDialog(detail = {}) {
            this.type = detail.type || 'info';
            this.title = detail.title || this.getDefaultTitle(this.type);
            this.message = detail.message || detail.text || '';
            this.okText = detail.okText || '{{ __('components.OK') }}';
            this.onOk = detail.onOk || null;
            this.open = true;
        },
        close() {
            this.open = false;
            this.onOk = null;
        },
        ok() {
            if (typeof this.onOk === 'function') {
                this.onOk();
            }
            this.close();
        },
        getDefaultTitle(type) {
            const titles = {
                success: '{{ __('components.Success') }}',
                error: '{{ __('components.Error') }}',
                warning: '{{ __('components.Warning') }}',
                info: '{{ __('components.Information') }}'
            };
            return titles[type] || titles.info;
        },
        getIconClass() {
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            return icons[this.type] || icons.info;
        },
        getColorClasses() {
            const colors = {
                success: { bg: 'bg-green-50 dark:bg-green-900/30', text: 'text-green-600 dark:text-green-200', btn: 'bg-green-600 hover:bg-green-700 focus:ring-green-500' },
                error: { bg: 'bg-red-50 dark:bg-red-900/30', text: 'text-red-600 dark:text-red-200', btn: 'bg-red-600 hover:bg-red-700 focus:ring-red-500' },
                warning: { bg: 'bg-amber-50 dark:bg-amber-900/30', text: 'text-amber-600 dark:text-amber-200', btn: 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-500' },
                info: { bg: 'bg-blue-50 dark:bg-blue-900/30', text: 'text-blue-600 dark:text-blue-200', btn: 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' }
            };
            return colors[this.type] || colors.info;
        }
    }"
    @alert-show.window="openDialog($event.detail)"
    x-cloak
>
    <div
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
        aria-modal="true"
        role="dialog"
    >
        <div class="fixed inset-0 bg-gray-900/60 dark:bg-black/70" aria-hidden="true"></div>

        <div
            x-show="open"
            x-transition
            class="relative w-full max-w-md bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl overflow-hidden"
        >
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                <span 
                    class="w-10 h-10 rounded-full flex items-center justify-center"
                    :class="getColorClasses().bg + ' ' + getColorClasses().text"
                >
                    <i class="fas" :class="getIconClass()"></i>
                </span>
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white" x-text="title"></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="message"></p>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/40 flex justify-end">
                <button
                    type="button"
                    class="px-6 py-2 rounded-lg text-sm font-semibold text-white focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900 transition-colors"
                    :class="getColorClasses().btn"
                    @click="ok()"
                >
                    <span x-text="okText"></span>
                </button>
            </div>
        </div>
    </div>
</div>
