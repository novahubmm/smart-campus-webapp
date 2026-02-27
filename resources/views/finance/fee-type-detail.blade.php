<x-app-layout>
    <x-slot name="header">
        <x-page-header
            icon="fas fa-tags"
            iconBg="bg-purple-50 dark:bg-purple-900/30"
            iconColor="text-purple-700 dark:text-purple-200"
            :subtitle="__('finance.Finance Management')"
            :title="__('finance.Fee Type Details')"
        />
    </x-slot>

    <div class="py-6 sm:py-10" x-data="feeTypeDetail()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-back-link 
                :href="route('student-fees.index', ['tab' => 'structure'])"
                :text="__('finance.Back to Student Fees')"
            />

            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800 dark:border-green-900/50 dark:bg-green-900/30 dark:text-green-100">
                    {{ session('status') }}
                </div>
            @endif

            <x-detail-header
                icon="fas fa-tags"
                iconBg="bg-purple-50 dark:bg-purple-900/30"
                iconColor="text-purple-600 dark:text-purple-400"
                :title="$feeType->name"
                :subtitle="__('finance.Fee Type') . ' - ' . $feeType->fee_type"
                :badge="$feeType->status ? __('finance.Active') : __('finance.Inactive')"
                :badgeColor="$feeType->status ? 'active' : 'inactive'"
                :editRoute="null"
                :deleteRoute="route('student-fees.categories.destroy', $feeType->id)"
                :deleteText="__('finance.Delete Fee Type')"
                :deleteTitle="__('finance.Delete Fee Type')"
                :deleteMessage="__('finance.Are you sure you want to delete this fee type? This action cannot be undone.')"
            >
                <x-slot name="actions">
                    <button type="button" 
                        @click="bulkSendInvoices()"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
                        <i class="fas fa-paper-plane"></i>
                        <span>{{ __('finance.Send Invoices') }}</span>
                    </button>
                    <button type="button" 
                        @click="openEditModal()"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg border border-purple-200 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-900/50 transition-colors">
                        <i class="fas fa-edit"></i>
                        <span>{{ __('finance.Edit Fee Type') }}</span>
                    </button>
                </x-slot>
            </x-detail-header>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <x-stat-card 
                    icon="fas fa-tags"
                    :title="__('finance.Fee Type')"
                    :number="$feeType->fee_type"
                    subtitle=""
                />
                
                <x-stat-card 
                    icon="fas fa-money-bill-wave"
                    :title="__('finance.Amount')"
                    :number="number_format($feeType->amount, 0) . ' MMK'"
                    :subtitle="__('finance.Per Month')"
                />
                
                <x-stat-card 
                    icon="fas fa-users"
                    :title="__('finance.Active Students')"
                    :number="$activeStudentsCount"
                    :subtitle="__('finance.Assigned')"
                />
            </div>

            <x-info-table 
                :title="__('finance.Fee Information')"
                :rows="[
                    [
                        'label' => __('finance.Code'),
                        'value' => $feeType->code ?? '—'
                    ],
                    [
                        'label' => __('finance.Frequency'),
                        'value' => $feeType->frequency ? ucfirst(str_replace('_', ' ', $feeType->frequency->frequency)) : '—'
                    ],
                    [
                        'label' => __('finance.Due Date'),
                        'value' => __('finance.Day') . ' ' . $feeType->due_date . ' ' . __('finance.of every month')
                    ],
                    [
                        'label' => __('finance.Start Month'),
                        'value' => $feeType->frequency && $feeType->frequency->start_month ? \Carbon\Carbon::create(null, $feeType->frequency->start_month, 1)->format('F') : '—'
                    ],
                    [
                        'label' => __('finance.End Month'),
                        'value' => $feeType->frequency && $feeType->frequency->end_month ? \Carbon\Carbon::create(null, $feeType->frequency->end_month, 1)->format('F') : '—'
                    ],
                    [
                        'label' => __('finance.Allow Partial Payment'),
                        'value' => $feeType->partial_status ? __('finance.Yes') : __('finance.No')
                    ],
                    [
                        'label' => __('finance.Allow Discount'),
                        'value' => $feeType->discount_status ? __('finance.Yes') : __('finance.No')
                    ],
                    [
                        'label' => __('finance.Description'),
                        'value' => $feeType->description
                    ],
                ]"
            />

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm" id="studentTableContainer">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('finance.Student Assignments') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('finance.Manage which students are assigned to pay this fee') }}</p>
                </div>

                <!-- Filters -->
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                    <div class="flex flex-wrap gap-3">
                        <!-- Search -->
                        <div class="flex-1 min-w-[200px]">
                            <input 
                                type="text" 
                                name="search" 
                                id="searchInput"
                                value="{{ request('search') }}"
                                placeholder="{{ __('finance.Search by name or student ID') }}"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent"
                                oninput="handleSearchChange()"
                            />
                        </div>

                        <!-- Grade Filter -->
                        <div class="w-40">
                            <select 
                                name="grade" 
                                id="gradeFilter"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent"
                                onchange="handleGradeChange()"
                            >
                                <option value="">{{ __('finance.All Grades') }}</option>
                                @foreach($grades as $grade)
                                    <option value="{{ $grade->id }}" {{ request('grade') == $grade->id ? 'selected' : '' }}>
                                        {{ $grade->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Class Filter -->
                        <div class="w-40">
                            <select 
                                name="class" 
                                id="classFilter"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed"
                                onchange="handleClassChange()"
                            >
                                <option value="">{{ __('finance.All Classes') }}</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ request('class') == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="w-40">
                            <select 
                                name="status" 
                                id="statusFilter"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent"
                                onchange="handleStatusChange()"
                            >
                                <option value="">{{ __('finance.All Status') }}</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('finance.Active') }}</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('finance.Inactive') }}</option>
                            </select>
                        </div>

                        <!-- Activate All Button (shown only when class is selected) -->
                        <button 
                            type="button"
                            id="activateAllBtn"
                            onclick="activateAllStudents()"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors"
                            style="display: {{ request('class') ? 'block' : 'none' }};"
                        >
                            <i class="fas fa-check-double mr-1"></i>{{ __('finance.Activate All') }}
                        </button>

                        <!-- Clear Button -->
                        <button 
                            type="button"
                            id="clearBtn"
                            onclick="clearFilters()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors"
                            style="display: {{ request()->hasAny(['search', 'grade', 'class', 'status']) ? 'block' : 'none' }};"
                        >
                            <i class="fas fa-times mr-1"></i>{{ __('finance.Clear') }}
                        </button>
                    </div>
                </div>

                <script>
                    let searchTimeout = null;
                    
                    function handleSearchChange() {
                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(() => {
                            loadStudents();
                        }, 500);
                    }
                    
                    function handleGradeChange() {
                        const gradeSelect = document.getElementById('gradeFilter');
                        const classSelect = document.getElementById('classFilter');
                        const selectedGrade = gradeSelect.value;
                        
                        // Disable class filter if "All Grades" is selected
                        if (selectedGrade === '') {
                            classSelect.disabled = true;
                            classSelect.value = '';
                        } else {
                            classSelect.disabled = false;
                            classSelect.value = '';
                        }
                        
                        // Hide Activate All button when grade changes
                        document.getElementById('activateAllBtn').style.display = 'none';
                        
                        // Load students with AJAX
                        loadStudents();
                    }
                    
                    function handleClassChange() {
                        const classSelect = document.getElementById('classFilter');
                        const activateAllBtn = document.getElementById('activateAllBtn');
                        
                        // Show/hide Activate All button based on class selection
                        if (classSelect.value) {
                            activateAllBtn.style.display = 'block';
                        } else {
                            activateAllBtn.style.display = 'none';
                        }
                        
                        // Load students with AJAX
                        loadStudents();
                    }
                    
                    function handleStatusChange() {
                        // Load students with AJAX
                        loadStudents();
                    }
                    
                    function clearFilters() {
                        document.getElementById('searchInput').value = '';
                        document.getElementById('gradeFilter').value = '';
                        document.getElementById('classFilter').value = '';
                        document.getElementById('statusFilter').value = '';
                        document.getElementById('classFilter').disabled = true;
                        document.getElementById('activateAllBtn').style.display = 'none';
                        document.getElementById('clearBtn').style.display = 'none';
                        
                        loadStudents();
                    }
                    
                    function loadStudents() {
                        const search = document.getElementById('searchInput').value;
                        const grade = document.getElementById('gradeFilter').value;
                        const classId = document.getElementById('classFilter').value;
                        const status = document.getElementById('statusFilter').value;
                        const clearBtn = document.getElementById('clearBtn');
                        
                        // Show/hide clear button
                        if (search || grade || classId || status) {
                            clearBtn.style.display = 'block';
                        } else {
                            clearBtn.style.display = 'none';
                        }
                        
                        // Build URL with query parameters
                        const params = new URLSearchParams();
                        if (search) params.append('search', search);
                        if (grade) params.append('grade', grade);
                        if (classId) params.append('class', classId);
                        if (status) params.append('status', status);
                        
                        const url = `{{ route('student-fees.categories.show', $feeType) }}?${params.toString()}`;
                        
                        // Update URL without reload
                        window.history.pushState({}, '', url);
                        
                        // Show loading state
                        const tableContainer = document.getElementById('studentTableContainer');
                        if (tableContainer) {
                            tableContainer.style.opacity = '0.5';
                            tableContainer.style.pointerEvents = 'none';
                        }
                        
                        // Fetch and update table
                        fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'text/html'
                            }
                        })
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            
                            // Find the table container by ID
                            const newTableContainer = doc.getElementById('studentTableContainer');
                            const currentTableContainer = document.getElementById('studentTableContainer');
                            
                            if (newTableContainer && currentTableContainer) {
                                // Replace the entire container content
                                currentTableContainer.innerHTML = newTableContainer.innerHTML;
                            }
                            
                            // Update class dropdown options if grade changed
                            const newClassSelect = doc.querySelector('#classFilter');
                            const currentClassSelect = document.getElementById('classFilter');
                            if (newClassSelect && currentClassSelect) {
                                // Save current selection
                                const currentValue = currentClassSelect.value;
                                // Update options
                                currentClassSelect.innerHTML = newClassSelect.innerHTML;
                                // Restore selection if it still exists
                                if (currentValue && Array.from(currentClassSelect.options).some(opt => opt.value === currentValue)) {
                                    currentClassSelect.value = currentValue;
                                } else {
                                    currentClassSelect.value = classId || '';
                                }
                                
                                // Update disabled state
                                if (!grade) {
                                    currentClassSelect.disabled = true;
                                } else {
                                    currentClassSelect.disabled = false;
                                }
                            }
                            
                            // Remove loading state
                            if (tableContainer) {
                                tableContainer.style.opacity = '1';
                                tableContainer.style.pointerEvents = 'auto';
                            }
                        })
                        .catch(error => {
                            console.error('Error loading students:', error);
                            // Remove loading state
                            if (tableContainer) {
                                tableContainer.style.opacity = '1';
                                tableContainer.style.pointerEvents = 'auto';
                            }
                            showNotification('{{ __("finance.An error occurred") }}', 'error');
                        });
                    }
                    
                    function activateAllStudents() {
                        const classId = document.getElementById('classFilter').value;
                        if (!classId) {
                            showNotification('{{ __("finance.Please select a class first") }}', 'error');
                            return;
                        }
                        
                        const activateAllBtn = document.getElementById('activateAllBtn');
                        const originalHtml = activateAllBtn.innerHTML;
                        
                        activateAllBtn.disabled = true;
                        activateAllBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>{{ __("finance.Processing...") }}';
                        
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        
                        fetch(`/student-fees/categories/{{ $feeType->id }}/activate-all`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                class_id: classId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(data.message, 'success');
                                loadStudents(); // Reload the table
                            } else {
                                showNotification(data.message || '{{ __("finance.An error occurred") }}', 'error');
                            }
                            activateAllBtn.innerHTML = originalHtml;
                            activateAllBtn.disabled = false;
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('{{ __("finance.An error occurred") }}', 'error');
                            activateAllBtn.innerHTML = originalHtml;
                            activateAllBtn.disabled = false;
                        });
                    }
                    
                    // Send invoice to student
                    function sendInvoice(feeTypeId, studentId) {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        
                        fetch(`/student-fees/categories/${feeTypeId}/students/${studentId}/send-invoice`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({})
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(data.message, 'success');
                                // Reload the student list to reflect changes
                                loadStudents();
                            } else {
                                showNotification(data.message || '{{ __("finance.An error occurred") }}', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('{{ __("finance.An error occurred") }}', 'error');
                        });
                    }
                    
                    // Initialize on page load
                    document.addEventListener('DOMContentLoaded', function() {
                        const gradeSelect = document.getElementById('gradeFilter');
                        const classSelect = document.getElementById('classFilter');
                        
                        // Disable class filter if "All Grades" is selected on page load
                        if (gradeSelect.value === '') {
                            classSelect.disabled = true;
                        }
                    });
                </script>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('finance.Student ID') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('finance.Student Name') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('finance.Grade') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('finance.Class') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('finance.Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($students as $student)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50" id="student-row-{{ $student->id }}">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $student->student_identifier ?? $student->student_id }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $student->user?->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $student->grade?->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $student->formatted_class_name }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($student->assignment_is_active)
                                                <button type="button" 
                                                    onclick="toggleStudent('{{ $feeType->id }}', '{{ $student->id }}', false)"
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg border border-red-300 dark:border-red-600 text-red-700 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors"
                                                    id="toggle-btn-{{ $student->id }}">
                                                    <i class="fas fa-times mr-1"></i>{{ __('finance.Deactivate') }}
                                                </button>
                                                <button type="button"
                                                    onclick="sendInvoice('{{ $feeType->id }}', '{{ $student->id }}')"
                                                    class="send-invoice-btn inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg border border-blue-300 dark:border-blue-600 text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors"
                                                    id="invoice-btn-{{ $student->id }}">
                                                    <i class="fas fa-paper-plane mr-1"></i>{{ __('finance.Send Invoice') }}
                                                </button>
                                            @else
                                                <button type="button" 
                                                    onclick="toggleStudent('{{ $feeType->id }}', '{{ $student->id }}', true)"
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg border border-green-300 dark:border-green-600 text-green-700 dark:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/30 transition-colors"
                                                    id="toggle-btn-{{ $student->id }}">
                                                    <i class="fas fa-check mr-1"></i>{{ __('finance.Activate') }}
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-users text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                            <p class="text-gray-500 dark:text-gray-400">{{ __('finance.No students found') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <x-table-pagination :paginator="$students" />
            </div>
        </div>

        <!-- Edit Fee Type Modal -->
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showEditModal = false">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-2xl shadow-2xl" @click.stop>
                    <form method="POST" action="{{ route('student-fees.categories.update', $feeType->id) }}" x-ref="editForm" @submit.prevent="submitEditForm">
                        @csrf
                        @method('PUT')
                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="fas fa-tags text-purple-600"></i>
                                <span>{{ __('finance.Edit Additional Fee') }}</span>
                            </h4>
                            <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" @click="showEditModal = false">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="p-5 space-y-4 max-h-[70vh] overflow-y-auto">
                            <!-- Row 1: Name & Fee Type -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Name') }} <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent" x-model="form.name" placeholder="{{ __('finance.e.g., Library Fee, Sport Fee') }}" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Fee Type') }} <span class="text-red-500">*</span></label>
                                    <select name="fee_type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent" x-model="form.fee_type" required>
                                        @foreach(\App\Models\FeeType::FEE_TYPES as $type)
                                            <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Row 2: Amount -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Amount') }} (MMK) <span class="text-red-500">*</span></label>
                                    <input type="number" name="amount" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent" x-model="form.amount" placeholder="0" min="0" step="1" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Status') }} <span class="text-red-500">*</span></label>
                                    <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent" x-model="form.status" required>
                                        <option value="active">{{ __('finance.Active') }}</option>
                                        <option value="inactive">{{ __('finance.Inactive') }}</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Row 4: Frequency -->
                            <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Frequency') }} <span class="text-red-500">*</span></label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <select name="frequency" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent" x-model="form.frequency" required>
                                            <option value="0">{{ __('finance.Choose Frequency') }}</option>
                                            <option value="one_time">{{ __('finance.This Month') }}</option>
                                            <option value="monthly">{{ __('finance.Monthly') }}</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Month Selection (shown only for monthly) -->
                                <div x-show="form.frequency === 'monthly'" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Start Month') }} <span class="text-red-500">*</span></label>
                                        <select name="start_month" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent" x-model="form.start_month" :required="form.frequency === 'monthly'">
                                            <option value="1" :disabled="1 < currentMonth">{{ __('finance.January') }}</option>
                                            <option value="2" :disabled="2 < currentMonth">{{ __('finance.February') }}</option>
                                            <option value="3" :disabled="3 < currentMonth">{{ __('finance.March') }}</option>
                                            <option value="4" :disabled="4 < currentMonth">{{ __('finance.April') }}</option>
                                            <option value="5" :disabled="5 < currentMonth">{{ __('finance.May') }}</option>
                                            <option value="6" :disabled="6 < currentMonth">{{ __('finance.June') }}</option>
                                            <option value="7" :disabled="7 < currentMonth">{{ __('finance.July') }}</option>
                                            <option value="8" :disabled="8 < currentMonth">{{ __('finance.August') }}</option>
                                            <option value="9" :disabled="9 < currentMonth">{{ __('finance.September') }}</option>
                                            <option value="10" :disabled="10 < currentMonth">{{ __('finance.October') }}</option>
                                            <option value="11" :disabled="11 < currentMonth">{{ __('finance.November') }}</option>
                                            <option value="12" :disabled="12 < currentMonth">{{ __('finance.December') }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.End Month') }} <span class="text-red-500">*</span></label>
                                        <select name="end_month" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent" x-model="form.end_month" :required="form.frequency === 'monthly'">
                                            <option value="1" :disabled="1 < currentMonth">{{ __('finance.January') }}</option>
                                            <option value="2" :disabled="2 < currentMonth">{{ __('finance.February') }}</option>
                                            <option value="3" :disabled="3 < currentMonth">{{ __('finance.March') }}</option>
                                            <option value="4" :disabled="4 < currentMonth">{{ __('finance.April') }}</option>
                                            <option value="5" :disabled="5 < currentMonth">{{ __('finance.May') }}</option>
                                            <option value="6" :disabled="6 < currentMonth">{{ __('finance.June') }}</option>
                                            <option value="7" :disabled="7 < currentMonth">{{ __('finance.July') }}</option>
                                            <option value="8" :disabled="8 < currentMonth">{{ __('finance.August') }}</option>
                                            <option value="9" :disabled="9 < currentMonth">{{ __('finance.September') }}</option>
                                            <option value="10" :disabled="10 < currentMonth">{{ __('finance.October') }}</option>
                                            <option value="11" :disabled="11 < currentMonth">{{ __('finance.November') }}</option>
                                            <option value="12" :disabled="12 < currentMonth">{{ __('finance.December') }}</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Validation Error Messages -->
                                <div x-show="form.frequency === 'monthly' && parseInt(form.start_month) < currentMonth" class="mt-2 p-2 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                                    <p class="text-sm text-red-600 dark:text-red-400 flex items-center gap-2">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span>{{ __('finance.Start month cannot be earlier than current month') }}</span>
                                    </p>
                                </div>
                                <div x-show="form.frequency === 'monthly' && parseInt(form.end_month) < currentMonth" class="mt-2 p-2 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                                    <p class="text-sm text-red-600 dark:text-red-400 flex items-center gap-2">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span>{{ __('finance.End month cannot be earlier than current month') }}</span>
                                    </p>
                                </div>
                                <div x-show="form.frequency === 'monthly' && parseInt(form.end_month) < parseInt(form.start_month)" class="mt-2 p-2 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                                    <p class="text-sm text-red-600 dark:text-red-400 flex items-center gap-2">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span>{{ __('finance.End month cannot be earlier than start month') }}</span>
                                    </p>
                                </div>
                            </div>

                            <!-- Row 5: Due Date -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Due Date') }} <span class="text-red-500">*</span></label>
                                    <select name="due_date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent" x-model="form.due_date" required>
                                        @for($day = 1; $day <= 28; $day++)
                                            <option value="{{ $day }}" :disabled="form.frequency === 'one_time' && {{ $day }} < currentDay">{{ $day }}</option>
                                        @endfor
                                    </select>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-show="form.frequency === 'monthly'">{{ __('finance.Every month on this day') }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-show="form.frequency === 'one_time'">{{ __('finance.Due date for this month') }}</p>
                                </div>
                            </div>
                            
                            <!-- Due Date Validation Error -->
                            <div x-show="form.frequency === 'one_time' && parseInt(form.due_date) < currentDay" class="p-2 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                                <p class="text-sm text-red-600 dark:text-red-400 flex items-center gap-2">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span>{{ __('finance.Due date cannot be earlier than today') }}</span>
                                </p>
                            </div>

                            <!-- Row 6: Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Description') }}</label>
                                <textarea name="description" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent" x-model="form.description" placeholder="{{ __('finance.Brief description of this fee...') }}"></textarea>
                            </div>

                            <!-- Row 5: Toggle Options -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-900/50">
                                    <input type="checkbox" name="partial_status" value="1" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 w-5 h-5" x-model="form.partial_status" id="partial_status_toggle">
                                    <label for="partial_status_toggle" class="cursor-pointer">
                                        <div class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('finance.Allow Partial') }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('finance.Partial payment') }}</div>
                                    </label>
                                </div>
                                <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-900/50">
                                    <input type="checkbox" name="discount_status" value="1" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 w-5 h-5" x-model="form.discount_status" id="discount_status_toggle">
                                    <label for="discount_status_toggle" class="cursor-pointer">
                                        <div class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('finance.Allow Discount') }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('finance.Discount eligible') }}</div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                            <button type="button" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="showEditModal = false">
                                <i class="fas fa-times mr-2"></i>{{ __('finance.Cancel') }}
                            </button>
                            <button type="submit" 
                                class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700"
                                :disabled="(form.frequency === 'monthly' && (parseInt(form.start_month) < currentMonth || parseInt(form.end_month) < currentMonth || parseInt(form.end_month) < parseInt(form.start_month))) || (form.frequency === 'one_time' && parseInt(form.due_date) < currentDay)"
                                :class="{'opacity-50 cursor-not-allowed': (form.frequency === 'monthly' && (parseInt(form.start_month) < currentMonth || parseInt(form.end_month) < currentMonth || parseInt(form.end_month) < parseInt(form.start_month))) || (form.frequency === 'one_time' && parseInt(form.due_date) < currentDay)}">
                                <i class="fas fa-save mr-2"></i>{{ __('finance.Save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function feeTypeDetail() {
            const currentMonth = new Date().getMonth() + 1; // 1-12
            const currentDay = new Date().getDate(); // 1-31
            return {
                showEditModal: false,
                currentDay: currentDay,
                currentMonth: currentMonth,
                form: {
                    name: @js($feeType->name),
                    fee_type: @js($feeType->fee_type),
                    amount: @js($feeType->amount),
                    due_date: @js($feeType->due_date),
                    status: @js($feeType->status ? 'active' : 'inactive'),
                    description: @js($feeType->description),
                    partial_status: @js((bool)$feeType->partial_status),
                    discount_status: @js((bool)$feeType->discount_status),
                    frequency: @js($feeType->frequency ? $feeType->frequency->frequency : 'monthly'),
                    start_month: @js($feeType->frequency ? $feeType->frequency->start_month : null) || currentMonth,
                    end_month: @js($feeType->frequency ? $feeType->frequency->end_month : null) || currentMonth,
                },
                openEditModal() {
                    this.showEditModal = true;
                },
                submitEditForm() {
                    const form = this.$refs.editForm;
                    const formData = new FormData(form);
                    
                    // Convert integer month values to Y-m format if frequency is monthly
                    if (this.form.frequency === 'monthly') {
                        const currentYear = new Date().getFullYear();
                        const startMonth = formData.get('start_month');
                        const endMonth = formData.get('end_month');
                        
                        if (startMonth && !startMonth.includes('-')) {
                            formData.set('start_month', currentYear + '-' + String(startMonth).padStart(2, '0'));
                        }
                        if (endMonth && !endMonth.includes('-')) {
                            formData.set('end_month', currentYear + '-' + String(endMonth).padStart(2, '0'));
                        }
                    } else {
                        formData.delete('start_month');
                        formData.delete('end_month');
                    }
                    
                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    }).then(response => {
                        if (response.ok && response.redirected) {
                            window.location.href = response.url;
                        } else if (response.ok) {
                            window.location.reload();
                        } else {
                            return response.json().then(data => {
                                let errorMessage = 'Validation errors:\n';
                                if (data.errors) {
                                    Object.keys(data.errors).forEach(key => {
                                        errorMessage += `${key}: ${data.errors[key].join(', ')}\n`;
                                    });
                                } else if (data.message) {
                                    errorMessage = data.message;
                                }
                                showNotification(errorMessage, 'error');
                            }).catch(() => { window.location.reload(); });
                        }
                    }).catch(error => {
                        console.error('Form submission error:', error);
                        showNotification('{{ __("finance.An error occurred. Please try again.") }}', 'error');
                    });
                },
                bulkSendInvoices() {
                    bulkSendInvoices();
                }
            }
        }

        // AJAX function to toggle student assignment
        function toggleStudent(feeTypeId, studentId, activate) {
            const button = document.getElementById('toggle-btn-' + studentId);
            const originalHtml = button.innerHTML;
            
            // Disable button and show loading
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>{{ __("finance.Processing...") }}';
            
            // Get CSRF token
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch(`/student-fees/categories/${feeTypeId}/students/${studentId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success dialog with reload on OK
                    if (typeof Alpine !== 'undefined') {
                        window.dispatchEvent(new CustomEvent('success-show', {
                            detail: {
                                title: '{{ __("finance.Success") }}',
                                message: data.message,
                                confirmText: '{{ __("finance.OK") }}',
                                onConfirm: () => {
                                    window.location.reload();
                                }
                            }
                        }));
                    } else {
                        alert(data.message);
                        window.location.reload();
                    }
                } else {
                    // Restore button
                    button.innerHTML = originalHtml;
                    button.disabled = false;
                    showNotification(data.message || '{{ __("finance.An error occurred") }}', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                button.innerHTML = originalHtml;
                button.disabled = false;
                showNotification('{{ __("finance.An error occurred") }}', 'error');
            });
        }

        // Show notification
        function showNotification(message, type) {
            // Create modal overlay
            const overlay = document.createElement('div');
            overlay.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm';
            overlay.style.animation = 'fadeIn 0.2s ease-out';
            
            // Create modal
            const modal = document.createElement('div');
            modal.className = 'bg-gray-800/95 dark:bg-gray-900/95 backdrop-blur-md rounded-2xl p-6 max-w-lg mx-4 shadow-2xl border border-gray-700/50';
            modal.style.animation = 'slideUp 0.3s ease-out';
            
            const iconColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            const icon = type === 'success' ? 'fa-check' : 'fa-times';
            const title = type === 'success' ? 'Success' : 'Error';
            
            modal.innerHTML = `
                <div class="flex items-start justify-between gap-6">
                    <div class="flex items-start gap-4 flex-1">
                        <div class="w-12 h-12 ${iconColor} rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas ${icon} text-white text-xl"></i>
                        </div>
                        <div class="flex-1 pt-1">
                            <h3 class="text-lg font-semibold text-white mb-1">${title}</h3>
                            <p class="text-gray-300 text-sm">${message}</p>
                        </div>
                    </div>
                    <button onclick="this.closest('.fixed').remove()" class="px-6 py-2.5 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition-colors flex-shrink-0 mt-2">
                        OK
                    </button>
                </div>
            `;
            
            overlay.appendChild(modal);
            document.body.appendChild(overlay);
            
            // Add CSS animations if not already present
            if (!document.getElementById('modal-animations')) {
                const style = document.createElement('style');
                style.id = 'modal-animations';
                style.textContent = `
                    @keyframes fadeIn {
                        from { opacity: 0; }
                        to { opacity: 1; }
                    }
                    @keyframes slideUp {
                        from { transform: translateY(20px); opacity: 0; }
                        to { transform: translateY(0); opacity: 1; }
                    }
                `;
                document.head.appendChild(style);
            }
        }

        // Bulk send invoices to all active students
        function bulkSendInvoices() {
            const feeTypeId = @js($feeType->id);
            const activeCount = @js($activeStudentsCount);
            
            if (activeCount === 0) {
                showNotification('{{ __("finance.No active students found for this fee type") }}', 'error');
                return;
            }
            
            // Use the confirm dialog component
            window.dispatchEvent(new CustomEvent('confirm-show', {
                detail: {
                    title: '{{ __("finance.Send Invoices") }}',
                    message: '{{ __("finance.Are you sure you want to send invoices to all") }} ' + activeCount + ' {{ __("finance.active students? Existing invoices will be skipped.") }}',
                    confirmText: '{{ __("finance.Send Invoices") }}',
                    cancelText: '{{ __("finance.Cancel") }}',
                    onConfirm: () => {
                        // Show loading overlay
                        const loadingOverlay = document.createElement('div');
                        loadingOverlay.className = 'fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center';
                        loadingOverlay.innerHTML = `
                            <div class="bg-gray-800 rounded-xl p-8 shadow-2xl max-w-md mx-4">
                                <div class="flex items-center gap-4">
                                    <i class="fas fa-spinner fa-spin text-blue-500 text-3xl"></i>
                                    <div>
                                        <h3 class="text-lg font-semibold text-white mb-1">{{ __("finance.Sending Invoices") }}</h3>
                                        <p class="text-gray-300 text-sm">{{ __("finance.Please wait...") }}</p>
                                    </div>
                                </div>
                            </div>
                        `;
                        document.body.appendChild(loadingOverlay);
                        
                        // Get CSRF token
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        
                        fetch(`/student-fees/categories/${feeTypeId}/bulk-send-invoices`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({})
                        })
                        .then(response => response.json())
                        .then(data => {
                            loadingOverlay.remove();
                            
                            if (data.success) {
                                showNotification(data.message, 'success');
                                // Reload page after 2 seconds to show updated data
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                showNotification(data.message || '{{ __("finance.An error occurred") }}', 'error');
                            }
                        })
                        .catch(error => {
                            loadingOverlay.remove();
                            console.error('Error:', error);
                            showNotification('{{ __("finance.An error occurred") }}', 'error');
                        });
                    }
                }
            }));
        }
    </script>

    <!-- Success Dialog Component -->
    <x-success-dialog />
</x-app-layout>
