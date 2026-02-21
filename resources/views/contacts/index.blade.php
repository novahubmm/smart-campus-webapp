<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-lg">
                <i class="fas fa-address-book"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('contacts.Support') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('contacts.Contact Us') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Contact Information Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                        <i class="fas fa-headset text-emerald-500"></i>
                        {{ __('contacts.Customer Support') }}
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">
                                    <i class="fas fa-phone mr-2 text-emerald-500"></i>{{ __('contacts.Phone') }}
                                </th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                    <a href="tel:+959979587680" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                                        +959979587680
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">
                                    <i class="fas fa-envelope mr-2 text-emerald-500"></i>{{ __('contacts.Email') }}
                                </th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                    <a href="mailto:support@smartcampusedu.com" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                                        support@smartcampusedu.com
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Support Hours Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                        <i class="fas fa-clock text-emerald-500"></i>
                        {{ __('contacts.Support Hours') }}
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('contacts.Monday - Friday') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">9:00 AM - 6:00 PM</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('contacts.Saturday') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">9:00 AM - 1:00 PM</td>
                            </tr>
                            <tr>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('contacts.Sunday') }}</th>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ __('contacts.Closed') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
