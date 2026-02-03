<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-school"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('settings.School Information') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="contactModal()">
        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl px-4 py-3 flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                    <span class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</span>
                </div>
            @endif

            <!-- School Profile Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('settings.School Profile') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('settings.Update the school brand, contact, and address details.') }}</p>
                </div>

                <form method="POST" action="{{ route('settings.school-info.update') }}">
                    @csrf

                    <!-- Form Fields -->
                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.School Name') }}</label>
                                <input type="text" name="school_name" value="{{ old('school_name', $setting?->school_name) }}" required
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('school_name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.School Email') }}</label>
                                <input type="email" name="school_email" value="{{ old('school_email', $setting?->school_email) }}"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('school_email')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.School Phone') }}</label>
                                <input type="text" name="school_phone" value="{{ old('school_phone', $setting?->school_phone) }}"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('school_phone')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.Principal') }}</label>
                                <input type="text" name="principal_name" value="{{ old('principal_name', $setting?->principal_name) }}"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('principal_name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.Website') }}</label>
                                <input type="text" name="school_website" value="{{ old('school_website', $setting?->school_website) }}"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('school_website')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.School Address') }}</label>
                            <textarea name="school_address" rows="3"
                                      class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('school_address', $setting?->school_address) }}</textarea>
                            @error('school_address')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.About Us') }}</label>
                            <textarea name="school_about_us" rows="3"
                                      class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('school_about_us', $setting?->school_about_us) }}</textarea>
                            @error('school_about_us')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-end gap-3">
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">
                            {{ __('settings.Cancel') }}
                        </a>
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm">
                            {{ __('settings.Save Changes') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Key Contacts Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('settings.Key Contacts') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('settings.Keep important school contacts up to date.') }}</p>
                    </div>
                    <button type="button" @click="openCreate()" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                        <i class="fas fa-plus"></i>{{ __('settings.Add Contact') }}
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('settings.Name') }}</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('settings.Role') }}</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('settings.Email') }}</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('settings.Phone') }}</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('settings.Status') }}</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('settings.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($contacts as $contact)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-5 py-4">
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ $contact->name }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-gray-600 dark:text-gray-300">{{ $contact->role ?? '—' }}</td>
                                    <td class="px-5 py-4 text-gray-600 dark:text-gray-300">{{ $contact->email ?? '—' }}</td>
                                    <td class="px-5 py-4 text-gray-600 dark:text-gray-300">{{ $contact->phone ?? '—' }}</td>
                                    <td class="px-5 py-4">
                                        @if($contact->is_primary)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">{{ __('settings.Primary') }}</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">{{ __('settings.Secondary') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button"
                                                    @click="openEdit({
                                                        id: '{{ $contact->id }}',
                                                        name: '{{ addslashes($contact->name) }}',
                                                        role: '{{ addslashes($contact->role) }}',
                                                        email: '{{ addslashes($contact->email) }}',
                                                        phone: '{{ addslashes($contact->phone) }}',
                                                        is_primary: {{ $contact->is_primary ? 'true' : 'false' }}
                                                    })"
                                                    class="w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 flex items-center justify-center" title="{{ __('settings.Edit') }}">
                                                <i class="fas fa-pen-to-square text-sm"></i>
                                            </button>
                                            <form method="POST" action="{{ route('settings.key-contacts.destroy', $contact) }}" class="inline"
                                                  @submit.prevent="$dispatch('confirm-show', {
                                                      title: '{{ __('settings.Delete contact') }}',
                                                      message: '{{ __('settings.Are you sure you want to remove this contact?') }}',
                                                      confirmText: '{{ __('settings.Delete') }}',
                                                      cancelText: '{{ __('settings.Cancel') }}',
                                                      onConfirm: () => $el.submit()
                                                  })">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 flex items-center justify-center" title="{{ __('settings.Delete') }}">
                                                    <i class="fas fa-trash text-sm"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">{{ __('settings.No contacts added yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Contact Modal -->
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="modalOpen = false">
            <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-lg shadow-2xl" @click.stop>
                <form :action="formAction" method="POST">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide" x-text="isEdit ? '{{ __('settings.Edit Contact') }}' : '{{ __('settings.New Contact') }}'"></p>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white" x-text="isEdit ? '{{ __('settings.Update Contact') }}' : '{{ __('settings.Add Contact') }}'"></h3>
                        </div>
                        <button type="button" class="w-9 h-9 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700" @click="modalOpen = false">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.Name') }}</label>
                            <input type="text" name="name" x-model="form.name" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.Role') }}</label>
                            <input type="text" name="role" x-model="form.role" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.Email') }}</label>
                                <input type="email" name="email" x-model="form.email" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.Phone') }}</label>
                                <input type="text" name="phone" x-model="form.phone" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" name="is_primary" value="1" x-model="form.is_primary" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
                            <span>{{ __('settings.Primary contact') }}</span>
                        </label>
                    </div>
                    <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 rounded-b-xl">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">
                            {{ __('settings.Cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm">
                            {{ __('settings.Save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function contactModal() {
            return {
                modalOpen: false,
                formAction: '{{ route('settings.key-contacts.store') }}',
                isEdit: false,
                contactId: null,
                form: { name: '', role: '', email: '', phone: '', is_primary: false },
                openCreate() {
                    this.isEdit = false;
                    this.formAction = '{{ route('settings.key-contacts.store') }}';
                    this.form = { name: '', role: '', email: '', phone: '', is_primary: false };
                    this.modalOpen = true;
                },
                openEdit(contact) {
                    this.isEdit = true;
                    this.contactId = contact.id;
                    this.formAction = '{{ url('/settings/key-contacts') }}/' + contact.id;
                    this.form = {
                        name: contact.name || '',
                        role: contact.role || '',
                        email: contact.email || '',
                        phone: contact.phone || '',
                        is_primary: contact.is_primary || false,
                    };
                    this.modalOpen = true;
                }
            }
        }
    </script>
</x-app-layout>
