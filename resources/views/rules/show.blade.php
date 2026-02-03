<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl shadow-lg"
                style="background: {{ $category->icon_bg_color ?? '#E2E8F0' }}; color: {{ $category->icon_color ?? '#64748B' }};">
                {{ $category->icon ?? '—' }}
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Rule Category') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $category->title }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10" x-data="ruleManager()">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ $category->description ?? __('No description provided.') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('rules.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <i class="fas fa-arrow-left"></i>{{ __('Back to Categories') }}
                    </a>
                    <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-emerald-600 hover:bg-emerald-700" @click="openModal()">
                        <i class="fas fa-plus"></i>{{ __('Add Rule') }}
                    </button>
                </div>
            </div>

            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl px-4 py-3 flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                    <span class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('status') }}</span>
                </div>
            @endif

            @php
                $severityCounts = [
                    'high' => $category->rules->where('severity', 'high')->count(),
                    'medium' => $category->rules->where('severity', 'medium')->count(),
                    'low' => $category->rules->where('severity', 'low')->count(),
                ];
                $nextOrder = ($category->rules->max('sort_order') ?? 0) + 1;
                $severityStyles = [
                    'high' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                    'medium' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                    'low' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                ];
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <x-stat-card
                    icon="fas fa-list"
                    :title="__('Total Rules')"
                    :number="$category->rules->count()"
                    :subtitle="__('In this category')"
                />
                <x-stat-card
                    icon="fas fa-exclamation-triangle"
                    :title="__('High Severity')"
                    :number="$severityCounts['high']"
                    :subtitle="__('Critical rules')"
                />
                <x-stat-card
                    icon="fas fa-exclamation-circle"
                    :title="__('Medium Severity')"
                    :number="$severityCounts['medium']"
                    :subtitle="__('Moderate rules')"
                />
                <x-stat-card
                    icon="fas fa-info-circle"
                    :title="__('Low Severity')"
                    :number="$severityCounts['low']"
                    :subtitle="__('Guidelines')"
                />
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Rules') }}</h3>
                </div>

                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('Order') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('Rule') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('Severity') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('Consequence') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($category->rules as $rule)
                                    <tr>
                                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">
                                            {{ $rule->sort_order }}
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $rule->text }}
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $severityStyles[$rule->severity] ?? '' }}">
                                                {{ ucfirst($rule->severity) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">
                                            {{ $rule->consequence ?? '—' }}
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center justify-end gap-1">
                                                <button type="button" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-blue-500 flex items-center justify-center hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30" title="{{ __('Edit') }}" @click="openEditModal(@js($rule))">
                                                    <i class="fas fa-pen text-xs"></i>
                                                </button>
                                                <button type="button" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-red-500 flex items-center justify-center hover:border-red-400 hover:bg-red-50 dark:hover:bg-red-900/30" title="{{ __('Delete') }}" @click="submitDelete('{{ $rule->id }}')">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                                <i class="fas fa-list text-4xl mb-3 opacity-50"></i>
                                                <p class="text-sm">{{ __('No rules added yet') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal()">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-lg shadow-2xl" @click.stop>
                    <form :action="formAction" method="POST">
                        @csrf
                        <template x-if="formMethod === 'PUT'"><input type="hidden" name="_method" value="PUT"></template>

                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-t-xl">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg">
                                    <i class="fas fa-list"></i>
                                </span>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="formMethod === 'PUT' ? '{{ __('Edit Rule') }}' : '{{ __('Add Rule') }}'"></h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Keep rules clear and concise.') }}</p>
                                </div>
                            </div>
                            <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" @click="closeModal()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="p-5 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Order') }}</label>
                                    <input type="number" name="order" min="1" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500" x-model="form.order">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Severity') }}</label>
                                    <select name="severity" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500" x-model="form.severity" required>
                                        <option value="low">{{ __('Low') }}</option>
                                        <option value="medium">{{ __('Medium') }}</option>
                                        <option value="high">{{ __('High') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Rule Text') }} <span class="text-red-500">*</span></label>
                                <textarea name="text" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500" x-model="form.text" required></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Consequence') }}</label>
                                <textarea name="consequence" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500" x-model="form.consequence"></textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                            <button type="button" class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="closeModal()">{{ __('Cancel') }}</button>
                            <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-emerald-600 hover:bg-emerald-700">
                                <i class="fas fa-check mr-2"></i><span x-text="formMethod === 'PUT' ? '{{ __('Update') }}' : '{{ __('Save') }}'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function ruleManager() {
            return {
                showModal: false,
                formMethod: 'POST',
                formAction: '{{ route('rules.items.store', $category) }}',
                form: {
                    order: '{{ $nextOrder }}',
                    text: '',
                    severity: 'medium',
                    consequence: ''
                },
                openModal() {
                    this.showModal = true;
                    this.formMethod = 'POST';
                    this.formAction = '{{ route('rules.items.store', $category) }}';
                    this.form = {
                        order: '{{ $nextOrder }}',
                        text: '',
                        severity: 'medium',
                        consequence: ''
                    };
                },
                openEditModal(rule) {
                    this.showModal = true;
                    this.formMethod = 'PUT';
                    this.formAction = '{{ url('rules') }}/{{ $category->id }}/items/' + rule.id;
                    this.form = {
                        order: rule.sort_order || '',
                        text: rule.text || '',
                        severity: rule.severity || 'medium',
                        consequence: rule.consequence || ''
                    };
                },
                closeModal() {
                    this.showModal = false;
                },
                submitDelete(id) {
                    if (!confirm('{{ __('Delete this rule?') }}')) return;
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ url('rules') }}/{{ $category->id }}/items/' + id;
                    form.innerHTML = `@csrf <input type="hidden" name="_method" value="DELETE">`;
                    document.body.appendChild(form);
                    form.submit();
                }
            };
        }
    </script>
</x-app-layout>
