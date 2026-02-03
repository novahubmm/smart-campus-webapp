<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg">
                <i class="fas fa-balance-scale"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('School Policies') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Rules & Regulations') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10" x-data="ruleCategoryManager()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl px-4 py-3 flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                    <span class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('status') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3 flex items-center gap-3">
                    <i class="fas fa-times-circle text-red-600 dark:text-red-400"></i>
                    <span class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-stat-card
                    icon="fas fa-layer-group"
                    :title="__('Total Categories')"
                    :number="$stats['total_categories']"
                    :subtitle="__('Rule groups')"
                />
                <x-stat-card
                    icon="fas fa-list"
                    :title="__('Total Rules')"
                    :number="$stats['total_rules']"
                    :subtitle="__('Active entries')"
                />
                <x-stat-card
                    icon="fas fa-check-circle"
                    :title="__('Populated Categories')"
                    :number="$stats['categories_with_rules']"
                    :subtitle="__('With rules')"
                />
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Rule Categories') }}</h3>
                    <div class="flex items-center gap-2">
                        <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg border border-emerald-200 text-emerald-700 hover:bg-emerald-50 dark:border-emerald-900/40 dark:text-emerald-300 dark:hover:bg-emerald-900/30" @click="openRuleModal()">
                            <i class="fas fa-plus"></i>{{ __('Add Rule') }}
                        </button>
                        <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-emerald-600 hover:bg-emerald-700" @click="openModal()">
                            <i class="fas fa-plus"></i>{{ __('Add Category') }}
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('Category') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('Preview Rules') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('Rules') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($categories as $category)
                                    <tr>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center gap-3">
                                                <span class="w-10 h-10 rounded-lg flex items-center justify-center text-base"
                                                    style="background: {{ $category->icon_bg_color ?? '#E2E8F0' }}; color: {{ $category->icon_color ?? '#64748B' }};">
                                                    {{ $category->icon ?? '—' }}
                                                </span>
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $category->title }}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $category->description ?? '—' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">
                                            <div class="space-y-1">
                                                @forelse($category->rules->take(2) as $rule)
                                                    <p class="truncate max-w-xs">{{ $rule->text }}</p>
                                                @empty
                                                    <span class="text-gray-400">—</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                {{ $category->rules_count }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center justify-end gap-1">
                                                <a href="{{ route('rules.show', $category) }}" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-emerald-600 flex items-center justify-center hover:border-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/30" title="{{ __('View Rules') }}">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </a>
                                                <button type="button" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-blue-500 flex items-center justify-center hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30" title="{{ __('Edit') }}" @click="openEditModal(@js([
                                                    'id' => $category->id,
                                                    'title' => $category->title,
                                                    'description' => $category->description,
                                                    'icon' => $category->icon,
                                                    'icon_color' => $category->icon_color,
                                                    'icon_bg_color' => $category->icon_bg_color,
                                                ]))">
                                                    <i class="fas fa-pen text-xs"></i>
                                                </button>
                                                <button type="button" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-red-500 flex items-center justify-center hover:border-red-400 hover:bg-red-50 dark:hover:bg-red-900/30" title="{{ __('Delete') }}" @click="submitDelete('{{ $category->id }}', {{ $category->rules_count }})">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                                <i class="fas fa-balance-scale text-4xl mb-3 opacity-50"></i>
                                                <p class="text-sm">{{ __('No rule categories found') }}</p>
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
                <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-md shadow-2xl" @click.stop>
                    <form :action="formAction" method="POST">
                        @csrf
                        <template x-if="formMethod === 'PUT'"><input type="hidden" name="_method" value="PUT"></template>

                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-t-xl">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg">
                                    <i class="fas fa-layer-group"></i>
                                </span>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="formMethod === 'PUT' ? '{{ __('Edit Category') }}' : '{{ __('Create Category') }}'"></h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Manage the category details') }}</p>
                                </div>
                            </div>
                            <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" @click="closeModal()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="p-5 space-y-4">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Title') }} <span class="text-red-500">*</span></label>
                                    <input type="text" name="title" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500" x-model="form.title" required>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Icon') }}</label>
                                        <input type="text" name="icon" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500" x-model="form.icon" placeholder="Icon">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Icon Color') }}</label>
                                        <input type="color" name="icon_color" class="w-full h-10 rounded-lg border-gray-300 dark:border-gray-600 cursor-pointer" x-model="form.icon_color">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Icon Background') }}</label>
                                        <input type="color" name="icon_bg_color" class="w-full h-10 rounded-lg border-gray-300 dark:border-gray-600 cursor-pointer" x-model="form.icon_bg_color">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Description') }}</label>
                                    <textarea name="description" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500" x-model="form.description"></textarea>
                                </div>
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

        <div x-show="showRuleModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeRuleModal()">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-md shadow-2xl" @click.stop>
                    <form :action="ruleFormAction" method="POST">
                        @csrf

                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-t-xl">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg">
                                    <i class="fas fa-list"></i>
                                </span>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Create Rule') }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Select a category and add the rule text.') }}</p>
                                </div>
                            </div>
                            <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" @click="closeRuleModal()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="p-5 space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Category') }} <span class="text-red-500">*</span></label>
                                <select name="category_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500" x-model="ruleForm.category_id" @change="updateRuleFormAction()" required>
                                    <option value="">{{ __('Select category') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Rule Text') }} <span class="text-red-500">*</span></label>
                                <textarea name="text" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500" x-model="ruleForm.text" required></textarea>
                            </div>
                            <input type="hidden" name="severity" value="medium">
                        </div>

                        <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                            <button type="button" class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="closeRuleModal()">{{ __('Cancel') }}</button>
                            <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-emerald-600 hover:bg-emerald-700" :disabled="!ruleFormAction">
                                <i class="fas fa-check mr-2"></i>{{ __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function ruleCategoryManager() {
            return {
                showModal: false,
                showRuleModal: false,
                formMethod: 'POST',
                formAction: '{{ route('rules.store') }}',
                form: {
                    title: '',
                    description: '',
                    icon: '',
                    icon_color: '#0891B2',
                    icon_bg_color: '#CFFAFE'
                },
                ruleFormAction: '',
                ruleForm: {
                    category_id: '',
                    text: ''
                },
                openModal() {
                    this.showModal = true;
                    this.formMethod = 'POST';
                    this.formAction = '{{ route('rules.store') }}';
                    this.form = {
                        title: '',
                        description: '',
                        icon: '',
                        icon_color: '#0891B2',
                        icon_bg_color: '#CFFAFE'
                    };
                },
                openRuleModal() {
                    const defaultCategory = '{{ $categories->first()?->id }}';
                    if (!defaultCategory) {
                        alert('{{ __('Please create a category first.') }}');
                        return;
                    }
                    this.showRuleModal = true;
                    this.ruleForm = {
                        category_id: defaultCategory,
                        text: ''
                    };
                    this.updateRuleFormAction();
                },
                openEditModal(category) {
                    this.showModal = true;
                    this.formMethod = 'PUT';
                    this.formAction = '{{ url('rules') }}/' + category.id;
                    this.form = {
                        title: category.title || '',
                        description: category.description || '',
                        icon: category.icon || '',
                        icon_color: category.icon_color || '#0891B2',
                        icon_bg_color: category.icon_bg_color || '#CFFAFE'
                    };
                },
                updateRuleFormAction() {
                    if (!this.ruleForm.category_id) {
                        this.ruleFormAction = '';
                        return;
                    }
                    this.ruleFormAction = '{{ url('rules') }}/' + this.ruleForm.category_id + '/items';
                },
                closeModal() {
                    this.showModal = false;
                },
                closeRuleModal() {
                    this.showRuleModal = false;
                },
                submitDelete(id, rulesCount) {
                    if (rulesCount > 0) {
                        alert('{{ __('Cannot delete category with existing rules. Please remove the rules first.') }}');
                        return;
                    }
                    if (!confirm('{{ __('Delete this category?') }}')) return;
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ url('rules') }}/' + id;
                    form.innerHTML = `@csrf <input type="hidden" name="_method" value="DELETE">`;
                    document.body.appendChild(form);
                    form.submit();
                }
            };
        }
    </script>
</x-app-layout>
