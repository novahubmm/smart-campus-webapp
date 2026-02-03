<x-app-layout>
    <link href="/css/select2.min.css" rel="stylesheet" />
    <x-slot name="styles">
        <link rel="stylesheet" href="{{ asset('css/academic-management.css') }}">
    </x-slot>
    <x-slot name="header">
        <x-page-header icon="fas fa-book" iconBg="bg-indigo-50 dark:bg-indigo-900/30" iconColor="text-indigo-700 dark:text-indigo-200" :subtitle="__('academic_management.Academic Management')" :title="__('academic_management.Subject Details')" />
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-back-link :href="route('academic-management.index', ['tab' => 'subjects'])" :text="__('academic_management.Back to Academic Management')" />

            @php
                $infoRows = [
                    ['label' => __('academic_management.Subject Code'), 'value' => e($subject->code)],
                    ['label' => __('academic_management.Subject Name'), 'value' => e($subject->name)],
                    ['label' => __('academic_management.Subject Type'), 'value' => e($subject->subjectType->name ?? '—')],
                    ['label' => __('academic_management.Grade'), 'value' => $subject->grades->isNotEmpty() ? e($subject->grades->pluck('level')->implode(', ')) : '—'],
                ];
            @endphp

            @php
                $subjectTypeName = $subject->subjectType->name ?? 'Core';
                $isCore = strtolower($subjectTypeName) === 'core';
                $badgeColor = $isCore ? 'info' : 'warning';
            @endphp
            <x-detail-header icon="fas fa-book" iconBg="bg-indigo-50 dark:bg-indigo-900/30" iconColor="text-indigo-600 dark:text-indigo-400" :title="$subject->name" :subtitle="__('academic_management.Code') . ': ' . $subject->code" :badge="$subjectTypeName" :badgeColor="$badgeColor" :editRoute="null" :deleteRoute="route('academic-management.subjects.destroy', $subject->id)" :deleteText="__('academic_management.Delete Subject')" :deleteTitle="__('academic_management.Delete Subject')" :deleteMessage="__('academic_management.Are you sure you want to delete this subject? This action cannot be undone.')">
                <x-slot name="actions">
                    <button type="button" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors" onclick="openModal('editSubjectModal')">
                        <i class="fas fa-edit"></i>
                        <span>{{ __('academic_management.Edit Subject') }}</span>
                    </button>
                </x-slot>
            </x-detail-header>

            <x-info-table :title="__('academic_management.Subject Information')" :rows="$infoRows" />

            <!-- Assigned Teachers -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('academic_management.Assigned Teachers') }}</h3>
                    <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700" onclick="openModal('assignTeacherModal')">
                        <i class="fas fa-user-plus"></i>{{ __('academic_management.Assign Teacher') }}
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Teacher Name') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Department') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('academic_management.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($subject->teachers as $teacher)
                                <tr>
                                    <td class="px-4 py-3"><span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $teacher->name ?? $teacher->user->name ?? '—' }}</span></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $teacher->department->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <form method="POST" action="{{ route('academic-management.subjects.teachers.detach', [$subject->id, $teacher->id]) }}" style="display: inline;">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-red-500 inline-flex items-center justify-center hover:bg-red-50 dark:hover:bg-red-900/30"><i class="fas fa-user-minus text-xs"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('academic_management.No teachers assigned yet') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <x-form-modal id="assignTeacherModal" title="{{ __('academic_management.Assign Teacher') }}" icon="fas fa-user-plus" action="{{ route('academic-management.subjects.teachers.attach', $subject->id) }}" method="POST" :submitText="__('academic_management.Assign Teacher')" :cancelText="__('academic_management.Cancel')">
                <div>
                    <label for="subjectTeacher" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('academic_management.Teacher') }} <span class="text-red-500">*</span></label>
                    <select id="subjectTeacher" name="teacher_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" required>
                        <option value="">{{ __('academic_management.Select Teacher') }}</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name ?? $teacher->user->name ?? '—' }}</option>
                        @endforeach
                    </select>
                </div>
            </x-form-modal>

            <x-form-modal id="editSubjectModal" title="{{ __('academic_management.Edit Subject') }}" icon="fas fa-book" action="{{ route('academic-management.subjects.update', $subject->id) }}" method="PUT" :submitText="__('academic_management.Update Subject')" :cancelText="__('academic_management.Cancel')">
                @include('academic.partials.subject-form-fields', ['subject' => $subject, 'subjectTypes' => $subjectTypes ?? collect(), 'grades' => $grades ?? collect()])
            </x-form-modal>

            <!-- Curriculum Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Curriculum / Table of Contents') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Add chapters and topics for this subject') }}</p>
                    </div>
                    @if(($subject->curriculumChapters ?? collect())->count() > 0)
                        <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 transition-colors" onclick="openModal('curriculumModal')">
                            <i class="fas fa-edit"></i>{{ __('Edit Curriculum') }}
                        </button>
                    @else
                        <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 transition-colors" onclick="openModal('curriculumModal')">
                            <i class="fas fa-plus"></i>{{ __('Add Curriculum') }}
                        </button>
                    @endif
                </div>
                <div class="p-4">
                    @forelse($subject->curriculumChapters ?? collect() as $chapter)
                        <div class="mb-3 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 flex items-center justify-between">
                                <h4 class="font-semibold text-gray-900 dark:text-white">{{ $chapter->order }}. {{ $chapter->title }}</h4>
                                <span class="text-xs text-gray-500 bg-gray-200 dark:bg-gray-600 px-2 py-0.5 rounded-full">{{ $chapter->topics->count() }} {{ __('topics') }}</span>
                            </div>
                            @if($chapter->topics->count() > 0)
                                <div class="p-3 space-y-1">
                                    @foreach($chapter->topics as $topic)
                                        <div class="text-sm text-gray-700 dark:text-gray-300 py-1 pl-4 flex items-center gap-2">
                                            <span class="text-gray-400 font-mono text-xs">{{ $chapter->order }}.{{ $topic->order }}</span>
                                            <span>{{ $topic->title }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                <i class="fas fa-book-open text-2xl text-gray-400 dark:text-gray-500"></i>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('No curriculum defined yet') }}</p>
                            <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors" onclick="openModal('curriculumModal')">
                                <i class="fas fa-plus"></i>{{ __('Add Curriculum') }}
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Curriculum Modal - Centered -->
            <div id="curriculumModal" class="fixed inset-0 z-50 hidden" aria-labelledby="curriculumModalTitle" role="dialog" aria-modal="true" style="display: none;">
                <div class="fixed inset-0 flex items-center justify-center p-4">
                    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal('curriculumModal')"></div>
                    <div class="relative w-full max-w-4xl bg-white dark:bg-gray-800 shadow-2xl rounded-2xl transform transition-all z-10 max-h-[95vh] overflow-hidden flex flex-col">
                        <!-- Fixed Header -->
                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center">
                                    <i class="fas fa-list-ol text-indigo-600 dark:text-indigo-400"></i>
                                </div>
                                <div>
                                    <h3 id="curriculumModalTitle" class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Edit Curriculum') }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $subject->name }}</p>
                                </div>
                            </div>
                            <button type="button" onclick="closeModal('curriculumModal')" class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-center transition-colors">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <form id="curriculumForm" method="POST" action="{{ route('curriculum.save', $subject->id) }}" class="flex flex-col flex-1 min-h-0">
                            @csrf
                            <!-- Scrollable Content Area -->
                            <div class="flex-1 overflow-y-auto p-5 relative" style="max-height: calc(95vh - 140px);" id="curriculum-scroll-area">
                                <!-- Scroll indicator -->
                                <div id="scroll-indicator" class="absolute top-0 right-0 w-full h-4 bg-gradient-to-b from-white dark:from-gray-800 to-transparent pointer-events-none opacity-0 transition-opacity duration-300 z-10"></div>
                                
                                <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-sm text-blue-700 dark:text-blue-300 flex items-start gap-2">
                                    <i class="fas fa-info-circle mt-0.5 flex-shrink-0"></i>
                                    <span>{{ __('Add chapters and topics for this subject. Press Tab to move quickly between fields. Scroll down to see all chapters.') }}</span>
                                </div>
                                <div id="chapters-container" class="space-y-4 mb-4"></div>
                                <button type="button" onclick="addChapter()" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                                    <i class="fas fa-plus"></i>{{ __('Add Chapter') }}
                                </button>
                                
                                <!-- Bottom scroll indicator -->
                                <div id="scroll-indicator-bottom" class="absolute bottom-0 right-0 w-full h-4 bg-gradient-to-t from-white dark:from-gray-800 to-transparent pointer-events-none opacity-0 transition-opacity duration-300 z-10"></div>
                            </div>
                            <!-- Fixed Footer -->
                            <div class="flex justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl flex-shrink-0">
                                <button type="button" onclick="closeModal('curriculumModal')" class="px-5 py-2.5 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">{{ __('Cancel') }}</button>
                                <button type="submit" class="px-5 py-2.5 text-sm font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-colors">
                                    <i class="fas fa-save mr-2"></i>{{ __('Save Curriculum') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('js/academic-management.js') }}"></script>
    <style>
        /* Custom scrollbar for curriculum modal */
        #curriculumModal .overflow-y-auto {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
        }
        
        #curriculumModal .overflow-y-auto::-webkit-scrollbar {
            width: 8px;
        }
        
        #curriculumModal .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        
        #curriculumModal .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        
        #curriculumModal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Dark mode scrollbar */
        .dark #curriculumModal .overflow-y-auto {
            scrollbar-color: #4b5563 #374151;
        }
        
        .dark #curriculumModal .overflow-y-auto::-webkit-scrollbar-track {
            background: #374151;
        }
        
        .dark #curriculumModal .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #4b5563;
        }
        
        .dark #curriculumModal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }
        
        /* Smooth scrolling */
        #curriculumModal .overflow-y-auto {
            scroll-behavior: smooth;
        }
        
        /* Chapter item animations */
        .chapter-item {
            animation: fadeInUp 0.3s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <script>
        let chapterCount = 0;
        const existingCurriculum = @json($subject->curriculumChapters ?? []);
        
        function addChapter(data = null) {
            const container = document.getElementById('chapters-container');
            const idx = chapterCount++;
            const id = data?.id || 'new';
            
            container.insertAdjacentHTML('beforeend', `
                <div class="chapter-item border border-gray-200 dark:border-gray-700 rounded-lg p-4" data-idx="${idx}">
                    <input type="hidden" name="chapters[${idx}][id]" value="${id}">
                    <div class="flex items-start gap-3 mb-3">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Chapter Title</label>
                            <input type="text" name="chapters[${idx}][title]" value="${data?.title || ''}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" placeholder="e.g., Chapter 1: Introduction" required>
                        </div>
                        <button type="button" onclick="removeChapter(this)" class="mt-6 w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-red-500 flex items-center justify-center hover:bg-red-50"><i class="fas fa-trash text-xs"></i></button>
                    </div>
                    <div class="ml-4 border-l-2 border-gray-200 dark:border-gray-600 pl-4">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-2">Topics</label>
                        <div class="topics-container space-y-2" data-idx="${idx}"></div>
                        <button type="button" onclick="addTopic(${idx})" class="mt-2 inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded border border-dashed border-gray-300 dark:border-gray-600 text-gray-500 hover:border-indigo-400 hover:text-indigo-600"><i class="fas fa-plus text-xs"></i>Add Topic</button>
                    </div>
                </div>
            `);
            
            if (data?.topics) data.topics.forEach(t => addTopic(idx, t));
            container.lastElementChild.querySelector('input[type="text"]').focus();
        }
        
        function addTopic(chapterIdx, data = null) {
            const container = document.querySelector(`.topics-container[data-idx="${chapterIdx}"]`);
            const idx = container.children.length;
            const id = data?.id || 'new';
            
            container.insertAdjacentHTML('beforeend', `
                <div class="topic-item flex items-center gap-2">
                    <input type="hidden" name="chapters[${chapterIdx}][topics][${idx}][id]" value="${id}">
                    <span class="text-gray-400 text-sm w-6">${idx + 1}.</span>
                    <input type="text" name="chapters[${chapterIdx}][topics][${idx}][title]" value="${data?.title || ''}" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm py-1.5" placeholder="Topic title" required>
                    <button type="button" onclick="removeTopic(this)" class="w-7 h-7 rounded border border-gray-200 dark:border-gray-600 text-red-400 flex items-center justify-center hover:bg-red-50"><i class="fas fa-times text-xs"></i></button>
                </div>
            `);
            container.lastElementChild.querySelector('input[type="text"]').focus();
        }
        
        function removeChapter(btn) { if(confirm('Remove this chapter?')) btn.closest('.chapter-item').remove(); }
        function removeTopic(btn) { btn.closest('.topic-item').remove(); const c = btn.closest('.topics-container'); c.querySelectorAll('.topic-item span').forEach((s,i) => s.textContent = (i+1)+'.'); }
        
        const origOpen = window.openModal;
        window.openModal = function(id) {
            if (id === 'curriculumModal') {
                document.getElementById('chapters-container').innerHTML = '';
                chapterCount = 0;
                if (existingCurriculum.length > 0) existingCurriculum.forEach(c => addChapter(c));
                else addChapter();
                
                // Show modal centered
                const modal = document.getElementById('curriculumModal');
                modal.classList.remove('hidden');
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                
                // Setup scroll indicators
                setupScrollIndicators();
                return;
            }
            origOpen(id);
        };
        
        const origClose = window.closeModal;
        window.closeModal = function(id) {
            if (id === 'curriculumModal') {
                const modal = document.getElementById('curriculumModal');
                modal.classList.add('hidden');
                modal.style.display = 'none';
                document.body.style.overflow = '';
                return;
            }
            origClose(id);
        };
        
        function setupScrollIndicators() {
            const scrollArea = document.getElementById('curriculum-scroll-area');
            const topIndicator = document.getElementById('scroll-indicator');
            const bottomIndicator = document.getElementById('scroll-indicator-bottom');
            
            function updateScrollIndicators() {
                const { scrollTop, scrollHeight, clientHeight } = scrollArea;
                const isScrollable = scrollHeight > clientHeight;
                
                if (!isScrollable) {
                    topIndicator.style.opacity = '0';
                    bottomIndicator.style.opacity = '0';
                    return;
                }
                
                // Show top indicator if not at top
                topIndicator.style.opacity = scrollTop > 20 ? '1' : '0';
                
                // Show bottom indicator if not at bottom
                const isAtBottom = scrollTop + clientHeight >= scrollHeight - 20;
                bottomIndicator.style.opacity = isAtBottom ? '0' : '1';
            }
            
            scrollArea.addEventListener('scroll', updateScrollIndicators);
            
            // Initial check
            setTimeout(updateScrollIndicators, 100);
            
            // Update when content changes
            const observer = new MutationObserver(updateScrollIndicators);
            observer.observe(document.getElementById('chapters-container'), {
                childList: true,
                subtree: true
            });
        }
    </script>
    @endpush
</x-app-layout>
