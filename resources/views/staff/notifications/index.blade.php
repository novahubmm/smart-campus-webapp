@php
    use Illuminate\Support\Str;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                <i class="fas fa-bell"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('staff.Staff Portal') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('staff.Notifications') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6" x-data="notificationManager()" x-init="init()">
        <div class="space-y-6">
            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800 dark:border-green-900/50 dark:bg-green-900/30 dark:text-green-100">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Stats (Dynamic with Alpine.js) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('staff.Total Notifications') }}</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white" x-text="totalCount"></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('staff.Unread') }}</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white" x-text="unreadCount"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('staff.Your Notifications') }}</h3>
                    <button x-show="unreadCount > 0" type="button" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30" @click="markAllAsRead()">
                        <i class="fas fa-check-double"></i>
                        {{ __('staff.Mark All as Read') }}
                    </button>
                </div>

                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('staff.Title') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('staff.Message') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('staff.Priority') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('staff.Status') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('staff.Date') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('staff.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody id="notifications-tbody" class="divide-y divide-gray-200 dark:divide-gray-700">
                                <template x-for="notification in notifications" :key="notification.id">
                                    <tr :class="notification.is_unread ? 'bg-blue-50 dark:bg-blue-900/20' : ''">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white flex-shrink-0">
                                                    <i class="fas fa-bullhorn text-xs"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate" x-text="notification.title"></p>
                                                    <span x-show="notification.is_unread" class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                        {{ __('staff.New') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <p class="text-sm text-gray-900 dark:text-gray-100 line-clamp-2 max-w-xs" x-text="notification.message"></p>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold"
                                                  :class="{
                                                      'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300': notification.priority === 'urgent',
                                                      'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300': notification.priority === 'high',
                                                      'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300': notification.priority === 'medium',
                                                      'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300': notification.priority === 'low'
                                                  }"
                                                  x-text="notification.priority.charAt(0).toUpperCase() + notification.priority.slice(1)">
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span x-show="notification.is_unread" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                {{ __('staff.Unread') }}
                                            </span>
                                            <span x-show="!notification.is_unread" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                {{ __('staff.Read') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                <div x-text="notification.created_at"></div>
                                                <div class="text-xs text-gray-600 dark:text-gray-400" x-text="notification.created_time"></div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-1">
                                                <a :href="'/staff/notifications/' + notification.id" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-blue-500 flex items-center justify-center hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30" title="{{ __('staff.View Details') }}">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </a>
                                                
                                                <a x-show="notification.announcement_id" :href="'/announcements/' + notification.announcement_id + '?from=notification'" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-green-500 flex items-center justify-center hover:border-green-400 hover:bg-green-50 dark:hover:bg-green-900/30" title="{{ __('staff.View Announcement') }}">
                                                    <i class="fas fa-bullhorn text-xs"></i>
                                                </a>
                                                
                                                <button x-show="notification.is_unread" type="button" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-amber-500 flex items-center justify-center hover:border-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/30" title="{{ __('staff.Mark as Read') }}" @click="markAsRead(notification.id)">
                                                    <i class="fas fa-check text-xs"></i>
                                                </button>
                                                
                                                <button type="button" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-red-500 flex items-center justify-center hover:border-red-400 hover:bg-red-50 dark:hover:bg-red-900/30" title="{{ __('staff.Delete') }}" @click="deleteNotification(notification.id)">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                
                                <!-- Empty state -->
                                <tr x-show="notifications.length === 0">
                                    <td colspan="6" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                            <i class="fas fa-bell-slash text-4xl mb-3 opacity-50"></i>
                                            <p class="text-sm">{{ __('staff.No notifications found') }}</p>
                                            <p class="text-xs mt-1">{{ __('staff.You\'ll see announcements and updates here') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div x-show="pagination.last_page > 1" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            {{ __('Showing') }}
                            <span class="font-medium" x-text="pagination.from"></span>
                            {{ __('to') }}
                            <span class="font-medium" x-text="pagination.to"></span>
                            {{ __('of') }}
                            <span class="font-medium" x-text="totalCount"></span>
                            {{ __('results') }}
                        </p>
                        <nav class="flex items-center gap-1">
                            <!-- Previous Button -->
                            <button type="button" 
                                    @click="goToPage(pagination.current_page - 1)" 
                                    :disabled="pagination.current_page === 1"
                                    :class="pagination.current_page === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
                                    class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-l-md">
                                <i class="fas fa-chevron-left text-xs"></i>
                            </button>
                            
                            <!-- Page Numbers -->
                            <template x-for="page in getPageNumbers()" :key="page">
                                <button type="button" 
                                        @click="page !== '...' && goToPage(page)"
                                        :class="{
                                            'bg-blue-600 text-white border-blue-600': page === pagination.current_page,
                                            'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700': page !== pagination.current_page && page !== '...',
                                            'cursor-default': page === '...'
                                        }"
                                        :disabled="page === '...'"
                                        class="relative inline-flex items-center px-3 py-2 text-sm font-medium border -ml-px"
                                        x-text="page">
                                </button>
                            </template>
                            
                            <!-- Next Button -->
                            <button type="button" 
                                    @click="goToPage(pagination.current_page + 1)" 
                                    :disabled="pagination.current_page === pagination.last_page"
                                    :class="pagination.current_page === pagination.last_page ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
                                    class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-r-md -ml-px">
                                <i class="fas fa-chevron-right text-xs"></i>
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function notificationManager() {
            return {
                notifications: [],
                totalCount: {{ $notifications->total() }},
                unreadCount: {{ $unreadCount }},
                pagination: {
                    current_page: 1,
                    last_page: 1,
                    per_page: 10,
                    from: 0,
                    to: 0
                },
                
                init() {
                    
                    
                    // Load initial data
                    this.fetchNotifications();
                    
                    // Listen for FCM notifications to update in real-time
                    window.addEventListener('fcm-notification-received', (event) => {
                        
                        
                        // Optimistic update - increment counts immediately
                        this.totalCount++;
                        this.unreadCount++;
                        
                        
                        // Then fetch actual data with retry to sync with database
                        this.fetchNotificationsWithRetry(3, 500);
                    });
                    
                    // Also listen for notification count updates from nav
                    window.addEventListener('notification-count-updated', (event) => {
                        
                        // Don't fetch here - it's already handled by fcm-notification-received
                    });
                    
                    
                },
                
                async fetchNotificationsWithRetry(maxRetries, delayMs) {
                    const expectedMinCount = this.totalCount;
                    
                    for (let attempt = 1; attempt <= maxRetries; attempt++) {
                        // Wait before fetching (give DB time to commit)
                        await new Promise(resolve => setTimeout(resolve, delayMs));
                        
                        
                        await this.fetchNotifications();
                        
                        // Check if we got the expected count
                        if (this.totalCount >= expectedMinCount - 1) {
                            
                            return;
                        }
                        
                        
                    }
                    
                    
                },
                
                async fetchNotifications(page = null) {
                    try {
                        const currentPage = page || this.pagination.current_page;
                        
                        const response = await fetch('{{ route("staff.notifications.list") }}?page=' + currentPage, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            this.notifications = data.notifications;
                            this.totalCount = data.total_count;
                            this.unreadCount = data.unread_count;
                            this.pagination = data.pagination;
                            
                        } else {
                            
                        }
                    } catch (error) {
                        
                    }
                },
                
                goToPage(page) {
                    if (page < 1 || page > this.pagination.last_page) return;
                    this.fetchNotifications(page);
                },
                
                getPageNumbers() {
                    const current = this.pagination.current_page;
                    const last = this.pagination.last_page;
                    const pages = [];
                    
                    if (last <= 7) {
                        for (let i = 1; i <= last; i++) pages.push(i);
                    } else {
                        if (current <= 3) {
                            pages.push(1, 2, 3, 4, '...', last);
                        } else if (current >= last - 2) {
                            pages.push(1, '...', last - 3, last - 2, last - 1, last);
                        } else {
                            pages.push(1, '...', current - 1, current, current + 1, '...', last);
                        }
                    }
                    
                    return pages;
                },

                async markAsRead(notificationId) {
                    try {
                        const response = await fetch(`/staff/notifications/${notificationId}/read`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        if (response.ok) {
                            // Update local state
                            const notification = this.notifications.find(n => n.id === notificationId);
                            if (notification) {
                                notification.is_unread = false;
                                this.unreadCount = Math.max(0, this.unreadCount - 1);
                            }
                            
                            // Update nav badge
                            if (typeof window.updateNotificationCount === 'function') {
                                window.updateNotificationCount();
                            }
                        }
                    } catch (error) {
                        
                    }
                },

                async markAllAsRead() {
                    try {
                        const response = await fetch('/staff/notifications/mark-all-read', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        if (response.ok) {
                            // Update local state
                            this.notifications.forEach(n => n.is_unread = false);
                            this.unreadCount = 0;
                            
                            // Update nav badge
                            if (typeof window.updateNotificationCount === 'function') {
                                window.updateNotificationCount();
                            }
                        }
                    } catch (error) {
                        
                    }
                },

                async deleteNotification(notificationId) {
                    if (!confirm('{{ __("staff.Are you sure you want to delete this notification?") }}')) {
                        return;
                    }

                    try {
                        const response = await fetch(`/staff/notifications/${notificationId}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        if (response.ok) {
                            // Update local state
                            const index = this.notifications.findIndex(n => n.id === notificationId);
                            if (index > -1) {
                                const wasUnread = this.notifications[index].is_unread;
                                this.notifications.splice(index, 1);
                                this.totalCount = Math.max(0, this.totalCount - 1);
                                if (wasUnread) {
                                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                                }
                            }
                            
                            // Refresh current page if empty and not on first page
                            if (this.notifications.length === 0 && this.pagination.current_page > 1) {
                                this.goToPage(this.pagination.current_page - 1);
                            } else {
                                // Refresh to get updated pagination
                                this.fetchNotifications();
                            }
                            
                            // Update nav badge
                            if (typeof window.updateNotificationCount === 'function') {
                                window.updateNotificationCount();
                            }
                        }
                    } catch (error) {
                        
                    }
                }
            };
        }
    </script>
</x-app-layout>
