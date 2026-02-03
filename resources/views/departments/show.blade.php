<x-app-layout>
    <x-slot name="header">
        <x-page-header 
            icon="fas fa-building"
            iconBg="bg-gradient-to-br from-blue-500 to-indigo-600"
            iconColor="text-white shadow-lg"
            subtitle="{{ __('departments.Department Details') }}"
            title="{{ $department->name }}"
        />
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Back Navigation -->
            <x-back-link 
                href="{{ route('departments.index') }}"
                text="{{ __('departments.Back to Departments') }}"
            />

            <!-- Department Header Card -->
            <x-detail-header 
                icon="fas fa-building"
                iconBg="bg-blue-50 dark:bg-blue-900/30"
                iconColor="text-blue-600 dark:text-blue-400"
                title="{{ $department->name }}"
                subtitle="{{ $department->code }}"
                badge="{{ $department->is_active ? __('Active') : __('Inactive') }}"
                badgeColor="{{ $department->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}"
            >
                <x-slot name="actions">
                    @can('manage departments')
                    <a href="{{ route('departments.edit', $department) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-edit"></i>{{ __('departments.Edit Department') }}
                    </a>
                    <form method="POST" action="{{ route('departments.destroy', $department) }}" class="inline"
                          @submit.prevent="$dispatch('confirm-show', {
                              title: '{{ __('departments.Delete department') }}',
                              message: '{{ __('departments.Are you sure you want to delete this department? This action cannot be undone.') }}',
                              confirmText: '{{ __('departments.Delete') }}',
                              cancelText: '{{ __('departments.Cancel') }}',
                              onConfirm: () => $el.submit()
                          })">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-red-600 hover:bg-red-700">
                            <i class="fas fa-trash"></i>{{ __('departments.Delete') }}
                        </button>
                    </form>
                    @endcan
                </x-slot>
            </x-detail-header>

            <!-- Department Summary -->
            @php
                $totalMembers = $department->members_count;
                $summaryRows = [
                    ['label' => __('departments.Department Name'), 'value' => $department->name],
                    ['label' => __('departments.Department Code'), 'value' => '<span class="font-mono">' . $department->code . '</span>'],
                    ['label' => __('departments.Status'), 'value' => $department->is_active ? __('Active') : __('Inactive')],
                    ['label' => __('departments.Members'), 'value' => $totalMembers . ' ' . __('departments.members')],
                    ['label' => __('departments.Created At'), 'value' => $department->created_at?->format('M d, Y') ?? '—'],
                    ['label' => __('departments.Updated At'), 'value' => $department->updated_at?->format('M d, Y') ?? '—'],
                ];
            @endphp
            <x-info-table title="{{ __('departments.Department Summary') }}" :rows="$summaryRows" />

            <!-- Department Members Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-users text-blue-500"></i>
                        <h4 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('departments.Members') }}</h4>
                    </div>
                    @can('manage departments')
                    <button type="button" onclick="openAddMemberModal()" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-semibold rounded-lg text-blue-600 bg-blue-50 dark:bg-blue-900/30 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/50">
                        <i class="fas fa-plus"></i>{{ __('departments.Add Member') }}
                    </button>
                    @endcan
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('departments.Name') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('departments.Role') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('departments.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700" id="membersTableBody">
                            @if($members->count() > 0)
                                @foreach($members as $member)
                                <tr data-member-id="{{ $member['id'] }}" data-member-type="{{ $member['type'] }}">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center space-x-3">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $member['type'] === 'teacher' ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400' }}">
                                                <i class="fas {{ $member['type'] === 'teacher' ? 'fa-chalkboard-teacher' : 'fa-user-tie' }} text-xs"></i>
                                            </span>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $member['name'] }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $member['employee_id'] }} • {{ $member['email'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $member['type'] === 'teacher' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-100' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100' }}">
                                                {{ ucfirst($member['type']) }}
                                            </span>
                                            @if($member['position'])
                                                <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">{{ $member['position'] }}</p>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            @can('manage departments')
                                            <button type="button" onclick="removeMember('{{ $member['id'] }}', '{{ $member['type'] }}')" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-red-500 flex items-center justify-center hover:border-red-400 hover:bg-red-50 dark:hover:bg-red-900/30" title="{{ __('Remove') }}">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('departments.No members assigned to this department yet.') }}
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($members->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
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
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Add Member Modal -->
    @can('manage departments')
    <div id="addMemberModal" class="fixed inset-0 z-50 hidden overflow-y-auto" @click.self="closeAddMemberModal()">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeAddMemberModal()"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-2xl shadow-2xl" onclick="event.stopPropagation()">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-t-xl">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                            <i class="fas fa-user-plus"></i>
                        </span>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('departments.Add Member') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('departments.Add a new member to this department') }}</p>
                        </div>
                    </div>
                    <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" onclick="closeAddMemberModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Modal Body -->
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('departments.Search Staff') }}</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                            <input type="text" id="memberSearchInput" class="w-full pl-10 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" placeholder="{{ __('departments.Search by name, ID...') }}" oninput="searchMembers(this.value)">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('departments.Select Member') }}</label>
                        <div id="memberSearchResults" class="max-h-60 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900/50">
                            <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-search text-2xl mb-2 block opacity-50"></i>
                                <p class="text-sm">{{ __('departments.Start typing to search for members') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div id="selectedMemberInfo" class="hidden p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg border border-blue-200 dark:border-blue-800">
                        <div class="flex items-center gap-3">
                            <div id="selectedMemberAvatar" class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold text-lg"></div>
                            <div class="flex-1">
                                <p id="selectedMemberName" class="font-semibold text-gray-900 dark:text-white"></p>
                                <p id="selectedMemberDetails" class="text-sm text-gray-500 dark:text-gray-400"></p>
                            </div>
                            <button type="button" onclick="clearSelectedMember()" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-gray-500 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                    <button type="button" onclick="closeAddMemberModal()" class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">{{ __('departments.Cancel') }}</button>
                    <button type="button" onclick="addSelectedMember()" id="addMemberBtn" disabled class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-plus mr-2"></i>{{ __('departments.Add Member') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endcan

    <script>
        let selectedMember = null;
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
        
        function openAddMemberModal() {
            document.getElementById('addMemberModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            document.getElementById('memberSearchInput').value = '';
            clearSelectedMember();
        }
        
        function closeAddMemberModal() {
            document.getElementById('addMemberModal').classList.add('hidden');
            document.body.style.overflow = '';
        }
        
        function searchMembers(query) {
            const resultsContainer = document.getElementById('memberSearchResults');
            
            clearTimeout(searchTimeout);
            
            if (!query.trim() || query.length < 2) {
                resultsContainer.innerHTML = `
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                        <i class="fas fa-search text-2xl mb-2 block opacity-50"></i>
                        <p class="text-sm">{{ __('departments.Start typing to search for members') }}</p>
                    </div>
                `;
                return;
            }
            
            resultsContainer.innerHTML = `
                <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2 block"></i>
                    <p class="text-sm">{{ __('departments.Searching...') }}</p>
                </div>
            `;
            
            searchTimeout = setTimeout(() => {
                fetchJson(`{{ route('departments.search-members') }}?search=${encodeURIComponent(query)}&department_id=${departmentId}`)
                    .then(data => {
                        displaySearchResults(data);
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                        resultsContainer.innerHTML = `
                            <div class="p-6 text-center text-red-500 dark:text-red-400">
                                <i class="fas fa-exclamation-triangle text-2xl mb-2 block"></i>
                                <p class="text-sm">Error searching for members</p>
                            </div>
                        `;
                    });
            }, 300);
        }
        
        function displaySearchResults(results) {
            const resultsContainer = document.getElementById('memberSearchResults');
            
            if (results.length === 0) {
                resultsContainer.innerHTML = `
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                        <i class="fas fa-info-circle text-2xl mb-2 block opacity-50"></i>
                        <p class="text-sm">{{ __('departments.No matching staff or teachers found.') }}</p>
                    </div>
                `;
                return;
            }
            
            resultsContainer.innerHTML = results.map(member => `
                <div class="p-3 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-200 dark:border-gray-600 last:border-b-0"
                     onclick="selectMember(${JSON.stringify(member).replace(/"/g, '&quot;')})">
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
        
        function selectMember(member) {
            selectedMember = member;
            
            const infoDiv = document.getElementById('selectedMemberInfo');
            const avatarDiv = document.getElementById('selectedMemberAvatar');
            const nameDiv = document.getElementById('selectedMemberName');
            const detailsDiv = document.getElementById('selectedMemberDetails');
            const addBtn = document.getElementById('addMemberBtn');
            
            const initials = member.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
            
            avatarDiv.textContent = initials;
            nameDiv.textContent = member.name;
            detailsDiv.textContent = `${member.type.charAt(0).toUpperCase() + member.type.slice(1)} • ${member.employee_id} • ${member.email}`;
            
            infoDiv.classList.remove('hidden');
            addBtn.disabled = false;
        }
        
        function clearSelectedMember() {
            selectedMember = null;
            document.getElementById('selectedMemberInfo').classList.add('hidden');
            document.getElementById('addMemberBtn').disabled = true;
        }
        
        function addSelectedMember() {
            if (!selectedMember) return;
            
            fetch(`{{ route('departments.add-member', $department) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    member_id: selectedMember.id,
                    member_type: selectedMember.type
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeAddMemberModal();
                    location.reload(); // Reload to show the new member
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
        
        function removeMember(memberId, memberType) {
            if (!confirm('{{ __("departments.Are you sure you want to remove this member from the department?") }}')) {
                return;
            }
            
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
                    // Remove the row from the table
                    const row = document.querySelector(`tr[data-member-id="${memberId}"][data-member-type="${memberType}"]`);
                    if (row) {
                        row.remove();
                    }
                    
                    // Check if there are no more members
                    const remainingRows = document.querySelectorAll('#membersTableBody tr[data-member-id]');
                    if (remainingRows.length === 0) {
                        document.getElementById('membersTableBody').innerHTML = `
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('departments.No members assigned to this department yet.') }}
                                </td>
                            </tr>
                        `;
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
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAddMemberModal();
            }
        });
    </script>
</x-app-layout>
