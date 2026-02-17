<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                <i class="fas fa-edit"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('departments.Academic') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('departments.Edit Department') }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-back-link 
                :href="route('departments.index')"
                :text="__('departments.Back to Departments')"
            />
            <!-- Department Information Form -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">{{ __('departments.Department Information') }}</h3>
                    
                    <form method="POST" action="{{ route('departments.update', $department) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('departments.Department Code') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="code" value="{{ old('code', $department->code) }}" required
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('departments.Short code for the department') }}</p>
                                @error('code')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('departments.Department Name') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $department->name) }}" required
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('departments.Full name of the department') }}</p>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="inline-flex items-center text-sm font-medium text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $department->is_active) ? 'checked' : '' }}
                                       class="rounded border-gray-300 dark:border-gray-700 text-blue-600 shadow-sm focus:ring-blue-500 dark:bg-gray-900 dark:text-gray-300">
                                <span class="ml-2">{{ __('departments.Active') }}</span>
                            </label>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3 sm:justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('departments.index') }}"
                               class="w-full sm:w-auto px-6 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-semibold rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-center">
                                <i class="fas fa-times mr-2"></i>{{ __('departments.Cancel') }}
                            </a>
                            <button type="submit"
                                    class="w-full sm:w-auto px-6 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-lg shadow-sm transition-all">
                                <i class="fas fa-check mr-2"></i>{{ __('departments.Update Department') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Department Members Management -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6 sm:p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('departments.Department Members') }}</h3>
                        <button type="button" id="add-member-btn" 
                                class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold rounded-lg shadow-sm transition-all">
                            <i class="fas fa-plus mr-2"></i>{{ __('departments.Add Member') }}
                        </button>
                    </div>

                    <!-- Add Member Form (Hidden by default) -->
                    <div id="add-member-form" class="hidden mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('departments.Search and Add Member') }}</h4>
                        
                        <div class="relative mb-4">
                            <input type="text" id="member-search" placeholder="{{ __('departments.Type name, email, or employee ID to search...') }}"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600">
                            <div id="search-results" class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg hidden max-h-60 overflow-y-auto"></div>
                        </div>

                        <div class="flex gap-2">
                            <button type="button" id="cancel-add-member" 
                                    class="px-4 py-2 bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-200 font-semibold rounded-lg hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors">
                                {{ __('departments.Cancel') }}
                            </button>
                        </div>
                    </div>

                    <!-- Current Members List -->
                    <div id="members-list">
                        @if($members->count() > 0)
                            <div class="space-y-3">
                                @foreach($members as $member)
                                    <div class="member-item flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600" 
                                         data-member-id="{{ $member['id'] }}" data-member-type="{{ $member['type'] }}">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex-shrink-0">
                                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full {{ $member['type'] === 'teacher' ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400' }}">
                                                    <i class="fas {{ $member['type'] === 'teacher' ? 'fa-chalkboard-teacher' : 'fa-user-tie' }}"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $member['name'] }}</h4>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ ucfirst($member['type']) }} • {{ $member['employee_id'] }} • {{ $member['email'] }}
                                                </p>
                                                @if($member['position'])
                                                    <p class="text-xs text-gray-600 dark:text-gray-300">{{ $member['position'] }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <button type="button" class="remove-member-btn text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                                                data-member-id="{{ $member['id'] }}" data-member-type="{{ $member['type'] }}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            
                            <!-- Pagination -->
                            @if($members->hasPages())
                            <div class="mt-6 flex items-center justify-between">
                                <div class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ __('Showing') }} {{ $members->firstItem() }} {{ __('to') }} {{ $members->lastItem() }} {{ __('of') }} {{ $members->total() }} {{ __('departments.members') }}
                                </div>
                                <div class="flex items-center space-x-2">
                                    {{-- Previous Page Link --}}
                                    @if ($members->onFirstPage())
                                        <span class="px-3 py-1 text-sm text-gray-400 dark:text-gray-600 cursor-not-allowed">
                                            <i class="fas fa-chevron-left"></i>
                                        </span>
                                    @else
                                        <a href="{{ $members->previousPageUrl() }}" class="px-3 py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @foreach ($members->getUrlRange(1, $members->lastPage()) as $page => $url)
                                        @if ($page == $members->currentPage())
                                            <span class="px-3 py-1 text-sm bg-blue-600 text-white rounded">{{ $page }}</span>
                                        @else
                                            <a href="{{ $url }}" class="px-3 py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">{{ $page }}</a>
                                        @endif
                                    @endforeach

                                    {{-- Next Page Link --}}
                                    @if ($members->hasMorePages())
                                        <a href="{{ $members->nextPageUrl() }}" class="px-3 py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    @else
                                        <span class="px-3 py-1 text-sm text-gray-400 dark:text-gray-600 cursor-not-allowed">
                                            <i class="fas fa-chevron-right"></i>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @endif
                        @else
                            <div id="no-members-message" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <i class="fas fa-users text-4xl mb-4"></i>
                                <p>{{ __('departments.No members assigned to this department yet.') }}</p>
                                <p class="text-sm">{{ __('departments.Click "Add Member" to assign staff or teachers.') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addMemberBtn = document.getElementById('add-member-btn');
            const addMemberForm = document.getElementById('add-member-form');
            const cancelAddMember = document.getElementById('cancel-add-member');
            const memberSearch = document.getElementById('member-search');
            const searchResults = document.getElementById('search-results');
            const membersList = document.getElementById('members-list');
            const noMembersMessage = document.getElementById('no-members-message');
            
            let searchTimeout;
            const departmentId = '{{ $department->id }}';

            function fetchJson(url, options = {}) {
                return fetch(url, options).then(async response => {
                    const contentType = response.headers.get('content-type') || '';
                    const bodyText = await response.text();

                    if (!response.ok) {
                        const message = bodyText && !bodyText.trim().startsWith('<')
                            ? bodyText.trim()
                            : `Request failed (${response.status})`;
                        throw new Error(message);
                    }

                    if (!contentType.includes('application/json')) {
                        const message = bodyText && !bodyText.trim().startsWith('<')
                            ? bodyText.trim()
                            : 'Unexpected response format.';
                        throw new Error(message);
                    }

                    return bodyText ? JSON.parse(bodyText) : [];
                });
            }

            // Show/hide add member form
            addMemberBtn.addEventListener('click', function() {
                addMemberForm.classList.remove('hidden');
                memberSearch.focus();
            });

            cancelAddMember.addEventListener('click', function() {
                addMemberForm.classList.add('hidden');
                memberSearch.value = '';
                searchResults.classList.add('hidden');
            });

            // Search functionality
            memberSearch.addEventListener('input', function() {
                const query = this.value.trim();
                
                clearTimeout(searchTimeout);
                
                if (query.length < 2) {
                    searchResults.classList.add('hidden');
                    return;
                }

                searchTimeout = setTimeout(() => {
                    fetchJson(`{{ route('departments.search-members') }}?search=${encodeURIComponent(query)}&department_id=${departmentId}`)
                        .then(data => {
                            displaySearchResults(data);
                        })
                        .catch(error => {
                            console.error('Search error:', error);
                            searchResults.innerHTML = '<div class="p-3 text-red-500 dark:text-red-400 text-sm">{{ __("departments.Error searching for members") }}</div>';
                            searchResults.classList.remove('hidden');
                        });
                }, 300);
            });

            function displaySearchResults(results) {
                if (results.length === 0) {
                    searchResults.innerHTML = '<div class="p-3 text-gray-500 dark:text-gray-400 text-sm">{{ __("departments.No matching staff or teachers found.") }}</div>';
                } else {
                    searchResults.innerHTML = results.map(member => `
                        <div class="search-result-item p-3 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-200 dark:border-gray-600 last:border-b-0"
                             data-member-id="${member.id}" data-member-type="${member.type}">
                            <div class="flex items-center space-x-3">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full ${member.type === 'teacher' ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400'}">
                                    <i class="fas ${member.type === 'teacher' ? 'fa-chalkboard-teacher' : 'fa-user-tie'} text-xs"></i>
                                </span>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">${member.name}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        ${member.type.charAt(0).toUpperCase() + member.type.slice(1)} • ${member.employee_id} • ${member.email}
                                    </div>
                                    ${member.position ? `<div class="text-xs text-gray-600 dark:text-gray-300">${member.position}</div>` : ''}
                                    ${member.current_department ? `<div class="text-xs text-orange-600 dark:text-orange-400">{{ __("departments.Currently in") }}: ${member.current_department}</div>` : ''}
                                </div>
                            </div>
                        </div>
                    `).join('');
                }
                
                searchResults.classList.remove('hidden');

                // Add click handlers to search results
                document.querySelectorAll('.search-result-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const memberId = this.dataset.memberId;
                        const memberType = this.dataset.memberType;
                        addMemberToDepartment(memberId, memberType);
                    });
                });
            }

            function addMemberToDepartment(memberId, memberType) {
                fetch(`{{ route('departments.add-member', $department) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        member_id: memberId,
                        member_type: memberType
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hide the add member form
                        addMemberForm.classList.add('hidden');
                        memberSearch.value = '';
                        searchResults.classList.add('hidden');

                        // Add the new member to the list
                        addMemberToList(data.member);

                        // Hide no members message if it exists
                        if (noMembersMessage) {
                            noMembersMessage.style.display = 'none';
                        }

                        // Show success message
                        showNotification(data.message, 'success');
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Add member error:', error);
                    showNotification('{{ __("departments.An error occurred while adding the member.") }}', 'error');
                });
            }

            function addMemberToList(member) {
                const memberHtml = `
                    <div class="member-item flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600" 
                         data-member-id="${member.id}" data-member-type="${member.type}">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full ${member.type === 'teacher' ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400'}">
                                    <i class="fas ${member.type === 'teacher' ? 'fa-chalkboard-teacher' : 'fa-user-tie'}"></i>
                                </span>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">${member.name}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    ${member.type.charAt(0).toUpperCase() + member.type.slice(1)} • ${member.employee_id} • ${member.email}
                                </p>
                                ${member.position ? `<p class="text-xs text-gray-600 dark:text-gray-300">${member.position}</p>` : ''}
                            </div>
                        </div>
                        <button type="button" class="remove-member-btn text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                                data-member-id="${member.id}" data-member-type="${member.type}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;

                // Check if members list has the space-y-3 container
                let membersContainer = membersList.querySelector('.space-y-3');
                if (!membersContainer) {
                    membersContainer = document.createElement('div');
                    membersContainer.className = 'space-y-3';
                    membersList.appendChild(membersContainer);
                }

                membersContainer.insertAdjacentHTML('beforeend', memberHtml);

                // Add event listener to the new remove button
                const newRemoveBtn = membersContainer.lastElementChild.querySelector('.remove-member-btn');
                newRemoveBtn.addEventListener('click', function() {
                    const memberId = this.dataset.memberId;
                    const memberType = this.dataset.memberType;
                    removeMemberFromDepartment(memberId, memberType, this.closest('.member-item'));
                });
            }

            // Remove member functionality
            document.addEventListener('click', function(e) {
                if (e.target.closest('.remove-member-btn')) {
                    const btn = e.target.closest('.remove-member-btn');
                    const memberId = btn.dataset.memberId;
                    const memberType = btn.dataset.memberType;
                    const memberItem = btn.closest('.member-item');
                    
                    if (confirm('{{ __("departments.Are you sure you want to remove this member from the department?") }}')) {
                        removeMemberFromDepartment(memberId, memberType, memberItem);
                    }
                }
            });

            function removeMemberFromDepartment(memberId, memberType, memberElement) {
                fetch(`{{ route('departments.remove-member', $department) }}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        member_id: memberId,
                        member_type: memberType
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the member from the list
                        memberElement.remove();

                        // Check if there are no more members
                        const remainingMembers = document.querySelectorAll('.member-item');
                        if (remainingMembers.length === 0 && noMembersMessage) {
                            noMembersMessage.style.display = 'block';
                        }

                        showNotification(data.message, 'success');
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Remove member error:', error);
                    showNotification('{{ __("departments.An error occurred while removing the member.") }}', 'error');
                });
            }

            function showNotification(message, type) {
                // Create a simple notification
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white`;
                notification.textContent = message;
                
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }

            // Hide search results when clicking outside
            document.addEventListener('click', function(e) {
                if (!memberSearch.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.add('hidden');
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
