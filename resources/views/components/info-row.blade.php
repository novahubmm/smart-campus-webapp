@props([
    'label' => '',
])

<tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
    <td class="px-6 py-4 text-sm font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap" style="width: 200px;">
        {{ $label }}
    </td>
    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
        {{ $slot }}
    </td>
</tr>
