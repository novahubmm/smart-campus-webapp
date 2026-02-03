<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-lg">
                <i class="fas fa-tags"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('event_categories.Events') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('event_categories.Event Categories') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10" x-data="categoryManager()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-back-link 
                :href="route('events.index')"
                :text="__('event_categories.Back to Events')"
            />

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-stat-card 
                    icon="fas fa-tags"
                    :title="__('event_categories.Total Categories')"
                    :number="$categories->count()"
                    :subtitle="__('event_categories.Available categories')"
                />
                
                <x-stat-card 
                    icon="fas fa-calendar-alt"
                    :title="__('event_categories.Active Events')"
                    :number="$categories->sum('events_count')"
                    :subtitle="__('event_categories.Using categories')"
                />
                
                <x-stat-card 
                    icon="fas fa-palette"
                    :title="__('event_categories.Color Themes')"
                    :number="$categories->pluck('color')->unique()->count()"
                    :subtitle="__('event_categories.Unique colors')"
                />
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('event_categories.All Categories') }}</h3>
                    <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700" @click="openModal()">
                        <i class="fas fa-plus"></i>{{ __('event_categories.Add Category') }}
                    </button>
                </div>

                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('event_categories.Category') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('event_categories.Color') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('event_categories.Icon') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('event_categories.Description') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('event_categories.Events') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('event_categories.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($categories as $category)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <span class="w-4 h-4 rounded-full flex-shrink-0" style="background: {{ $category->color ?? '#6b7280' }};"></span>
                                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $category->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-mono" style="background: {{ $category->color ?? '#6b7280' }}22; color: {{ $category->color ?? '#6b7280' }};">{{ $category->color ?? '—' }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                            @if($category->icon)<i class="{{ $category->icon }} mr-1"></i><span class="text-xs text-gray-500">{{ $category->icon }}</span>@else — @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 max-w-xs truncate">{{ $category->description ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">{{ $category->events_count ?? 0 }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-1">
                                                <button type="button" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-blue-500 flex items-center justify-center hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30" title="{{ __('event_categories.Edit') }}" @click="openEditModal(@js($category))">
                                                    <i class="fas fa-pen text-xs"></i>
                                                </button>
                                                <button type="button" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-red-500 flex items-center justify-center hover:border-red-400 hover:bg-red-50 dark:hover:bg-red-900/30" title="{{ __('event_categories.Delete') }}" @click="submitDelete('{{ $category->id }}', {{ $category->events_count ?? 0 }})">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                                <i class="fas fa-tags text-4xl mb-3 opacity-50"></i>
                                                <p class="text-sm">{{ __('event_categories.No categories found') }}</p>
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

        <!-- Create/Edit Category Modal -->
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal()">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-md shadow-2xl" @click.stop>
                    <form :action="formAction" method="POST" x-ref="categoryForm">
                        @csrf
                        <template x-if="formMethod === 'PUT'"><input type="hidden" name="_method" value="PUT"></template>
                        
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-t-xl">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-lg">
                                    <i class="fas fa-tags"></i>
                                </span>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="formMethod === 'PUT' ? '{{ __('event_categories.Edit Category') }}' : '{{ __('event_categories.Create Category') }}'"></h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('event_categories.Fill in the category details') }}</p>
                                </div>
                            </div>
                            <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" @click="closeModal()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <!-- Modal Body -->
                        <div class="p-5 space-y-4">
                            <!-- Category Details Section -->
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ __('event_categories.Category Details') }}</h4>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('event_categories.Name') }} <span class="text-red-500">*</span></label>
                                        <input type="text" name="name" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500" x-model="form.name" placeholder="{{ __('event_categories.Enter category name') }}" required>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('event_categories.Color') }}</label>
                                            <div class="flex items-center gap-2">
                                                <input type="color" name="color" class="w-12 h-10 rounded-lg border-gray-300 dark:border-gray-600 cursor-pointer" x-model="form.color">
                                                <input type="text" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm font-mono" x-model="form.color" readonly>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('event_categories.Icon') }}</label>
                                            <input type="text" name="icon" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500" x-model="form.icon" placeholder="fas fa-star">
                                            <p class="text-xs text-gray-500 mt-1">{{ __('event_categories.FontAwesome class e.g. fas fa-star') }}</p>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('event_categories.Description') }}</label>
                                        <textarea name="description" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500" x-model="form.description" placeholder="{{ __('event_categories.Optional description...') }}"></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Preview Section -->
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                    <i class="fas fa-eye text-indigo-500"></i>{{ __('event_categories.Preview') }}
                                </h4>
                                <div class="flex items-center gap-2">
                                    <span class="w-4 h-4 rounded-full" :style="'background:' + form.color"></span>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white" x-text="form.name || '{{ __('event_categories.Category Name') }}'"></span>
                                    <template x-if="form.icon"><i :class="form.icon" class="text-gray-500"></i></template>
                                </div>
                                <template x-if="form.description">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2" x-text="form.description"></p>
                                </template>
                            </div>
                        </div>
                        
                        <!-- Modal Footer -->
                        <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                            <button type="button" class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="closeModal()">{{ __('event_categories.Cancel') }}</button>
                            <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700">
                                <i class="fas fa-check mr-2"></i><span x-text="formMethod === 'PUT' ? '{{ __('event_categories.Update') }}' : '{{ __('event_categories.Save') }}'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function categoryManager() {
            return {
                showModal: false,
                formMethod: 'POST',
                formAction: '{{ route('event-categories.store') }}',
                form: { name: '', color: '#4285f4', icon: '', description: '' },
                openModal() {
                    this.showModal = true;
                    this.formMethod = 'POST';
                    this.formAction = '{{ route('event-categories.store') }}';
                    this.form = { name: '', color: '#4285f4', icon: '', description: '' };
                },
                openEditModal(category) {
                    this.showModal = true;
                    this.formMethod = 'PUT';
                    this.formAction = '{{ url('event-categories') }}/' + category.id;
                    this.form = {
                        name: category.name || '',
                        color: category.color || '#4285f4',
                        icon: category.icon || '',
                        description: category.description || ''
                    };
                },
                closeModal() { 
                    this.showModal = false; 
                },
                submitDelete(id, eventsCount) {
                    if (eventsCount > 0) {
                        alert('{{ __('event_categories.Cannot delete category with existing events. Please reassign or delete the events first.') }}');
                        return;
                    }
                    if (!confirm('{{ __('event_categories.Delete this category?') }}')) return;
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ url('event-categories') }}/' + id;
                    form.innerHTML = `@csrf <input type="hidden" name="_method" value="DELETE">`;
                    document.body.appendChild(form);
                    form.submit();
                }
            };
        }
    </script>
</x-app-layout>
