@props(['message' => null])

<!-- Modal Error Alert -->
<div
    x-data="{ show: false, message: '{{ $message }}' }"
    x-show="show"
    x-cloak
    @show-error.window="show = true; message = $event.detail.message"
    class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4"
    style="display: none;"
    @click.self="show = false"
    @keydown.escape.window="show = false"
>
    <div class="bg-gray-800 rounded-xl shadow-2xl max-w-lg w-full p-6"
         @click.stop
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-90"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-90">
        
        <!-- Header with icon and title -->
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 bg-red-900">
                <i class="fas fa-exclamation-circle text-white text-xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-white">Error</h3>
        </div>
        
        <!-- Message -->
        <p class="text-gray-300 text-sm leading-relaxed mb-6" x-text="message">{{ $message ?? $slot }}</p>
        
        <!-- Button container aligned to right -->
        <div class="flex justify-end">
            <button @click="show = false"
                    class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 px-8 rounded-lg transition-colors">
                OK
            </button>
        </div>
    </div>
</div>
