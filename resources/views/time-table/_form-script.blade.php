@php
    $classOptions = $classes
        ->map(function ($c) {
            // Format: "Grade X (Section)" or just "Grade X" if no section
            $gradeLevel = $c->grade?->level;
            $section = null;
            
            // Extract section from class name (e.g., "Grade 1 A" -> "A")
            if ($c->name && preg_match('/\s([A-Za-z])$/i', $c->name, $matches)) {
                $section = strtoupper($matches[1]);
            }
            
            // Build localized label
            $label = $gradeLevel !== null 
                ? \App\Helpers\SectionHelper::formatClassName($gradeLevel, $section)
                : ($c->name ?? '');
            
            return [
                'id' => $c->id,
                'label' => $label,
                'grade_id' => $c->grade_id,
                'batch_id' => $c->batch_id,
            ];
        })
        ->values();

    $classSubjects = $classSubjects ?? collect();
    $defaults = $defaults ?? [];
@endphp

<script>
const baseTimetableConfig = {
    storeUrl: @json($storeUrl),
    updateUrl: @json($updateUrl ?? null),
    initialClassId: @json($initialClassId ?? null),
};

function timetableForm(config = {}) {
    const mergedConfig = { ...baseTimetableConfig, ...config };
    const classes = @json($classOptions);
    const classSubjects = @json($classSubjects);
    const grades = @json($grades ?? []);
    const gradeSubjects = @json($gradeSubjects ?? []);
    const allTimetablesByClass = @json($allTimetablesByClass ?? []);
    const defaults = @json($defaults);
    const periodLabel = @json(__('time_table.Period'));
    const breakLabel = @json(__('time_table.Break'));
    const failedToSaveMessage = @json(__('time_table.Failed to save timetable'));
    const failedToDeleteMessage = @json(__('time_table.Failed to delete timetable'));
    const defaultWeekDays = defaults['week_days'] ?? ['mon', 'tue', 'wed', 'thu', 'fri'];
    const defaultPeriodCount = parseInt(defaults['number_of_periods_per_day']) || 8;
    const defaultMinutesPerPeriod = parseInt(defaults['minute_per_period']) || 45;
    const defaultBreakDuration = parseInt(defaults['break_duration']) || 15;
    const defaultStartTime = defaults['school_start_time'] || '08:00';
    const defaultEndTime = defaults['school_end_time'] || '15:00';

    return {
        config: mergedConfig,
        defaults,
        classes,
        gradeSubjects,
        classSubjects,
        allTimetablesByClass,
        selection: { class_id: '' },
        timetables: [],
        allDays: ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'],

        // Modals state
        subjectModalOpen: false,
        periodEditModalOpen: false,
        settingsModalOpen: false,
        subjectSearch: '',
        
        // Current editing context
        currentTimetableIdx: null,
        currentDay: null,
        currentPeriodIdx: null,
        
        // Period edit data
        periodEditData: {
            label: '',
            starts_at: '',
            ends_at: '',
            is_break: false
        },
        
        // Settings data
        settingsData: {
            school_start_time: defaultStartTime,
            school_end_time: defaultEndTime,
            minutes_per_period: defaultMinutesPerPeriod,
            break_duration: defaultBreakDuration,
            week_days: [...defaultWeekDays]
        },

        generateUUID() {
            if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
                return crypto.randomUUID();
            }
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                const r = Math.floor(Math.random() * 16);
                const v = c === 'x' ? r : (r & 0x3) | 0x8;
                return v.toString(16);
            });
        },

        parseTime(timeStr) {
            if (!timeStr || !timeStr.includes(':')) return null;
            const [h, m] = timeStr.split(':').map(Number);
            if (Number.isNaN(h) || Number.isNaN(m)) return null;
            return h * 60 + m;
        },

        minutesToTime(totalMinutes) {
            const h = Math.floor((totalMinutes % (24 * 60)) / 60);
            const m = totalMinutes % 60;
            const pad = (v) => v.toString().padStart(2, '0');
            return `${pad(h)}:${pad(m)}`;
        },

        initPage() {
            if (mergedConfig.initialClassId) {
                this.selection.class_id = mergedConfig.initialClassId;
                // Auto-select the class in dropdown and load timetables
                this.$nextTick(() => {
                    this.createTimetable();
                });
            }
        },

        // Generate default periods based on settings
        generateDefaultPeriods(startTime, minutesPerPeriod, breakDuration, count) {
            const periods = [];
            let currentMinutes = this.parseTime(startTime) || 480; // 8:00 AM default
            
            for (let i = 0; i < count; i++) {
                const periodStart = this.minutesToTime(currentMinutes);
                const periodEnd = this.minutesToTime(currentMinutes + minutesPerPeriod);
                
                periods.push({
                    label: `${periodLabel} ${i + 1}`,
                    starts_at: periodStart,
                    ends_at: periodEnd,
                    is_break: false
                });
                
                // Only add break duration between periods 1-7, not after period 7
                if (i < 7) { // periods 1-7 (0-6 in array)
                    currentMinutes += minutesPerPeriod + breakDuration;
                } else {
                    // Period 8 and beyond: no break, start immediately after previous period ends
                    currentMinutes += minutesPerPeriod;
                }
            }
            
            return periods;
        },

        createTimetable() {
            const cls = this.classes.find(c => c.id === this.selection.class_id);
            if (!cls) {
                alert('{{ __('time_table.Please select a class') }}');
                return;
            }

            // Check if we already loaded timetables for this class
            const alreadyLoaded = this.timetables.some(t => t.class_id === cls.id);
            if (alreadyLoaded) {
                // Just open the new one (last one for this class that's not saved)
                const newIdx = this.timetables.findIndex(t => t.class_id === cls.id && t.isNew);
                if (newIdx !== -1) {
                    this.timetables[newIdx].isOpen = true;
                }
                return;
            }

            const subjects = this.gradeSubjects[cls.grade_id] || this.classSubjects[cls.id] || [];
            const classVersions = this.allTimetablesByClass[cls.id] || [];
            
            // Load existing versions as read-only entries
            classVersions.forEach(existing => {
                const periods = existing.periods?.length > 0 
                    ? this.convertSeedPeriods(existing.periods)
                    : [];

                // Build a mapping from period_number to array index
                const periodNumberToIndex = {};
                if (Array.isArray(existing.periods)) {
                    const uniquePeriodNumbers = [...new Set(existing.periods.map(p => p.period_number))].sort((a, b) => a - b);
                    uniquePeriodNumbers.forEach((pNum, idx) => {
                        periodNumberToIndex[pNum] = idx;
                    });
                }

                // Build entries from existing periods with subject/teacher names
                const entries = {};
                if (Array.isArray(existing.periods)) {
                    existing.periods.forEach((p) => {
                        const periodIdx = periodNumberToIndex[p.period_number] ?? (p.period_number - 1);
                        const key = this.cellKey(p.day_of_week, periodIdx);
                        entries[key] = {
                            subject_id: p.subject_id || '',
                            teacher_profile_id: p.teacher_profile_id || '',
                            subject_name: p.subject_name || '',
                            teacher_name: p.teacher_name || '',
                        };
                    });
                }

                this.timetables.push({
                    local_id: this.generateUUID(),
                    id: existing.id,
                    display_label: cls.label,
                    batch_id: cls.batch_id,
                    grade_id: cls.grade_id,
                    class_id: cls.id,
                    version: existing.version,
                    version_name: existing.version_name,
                    status: existing.status,
                    is_active: existing.is_active,
                    week_days: existing.week_days ?? [...defaultWeekDays],
                    school_start_time: existing.school_start_time ?? defaultStartTime,
                    school_end_time: existing.school_end_time ?? defaultEndTime,
                    minutes_per_period: existing.minutes_per_period ?? defaultMinutesPerPeriod,
                    break_duration: existing.break_duration ?? defaultBreakDuration,
                    periods,
                    subjects,
                    entries,
                    isOpen: false,
                    isDirty: false,
                    isNew: false,
                    isExisting: true,
                    lastChange: null,
                });
            });

            // Calculate next version number
            const existingVersionNums = classVersions.map(t => t.version || 1);
            const nextVersion = existingVersionNums.length > 0 ? Math.max(...existingVersionNums) + 1 : 1;

            // Get active timetable as seed for period structure only (not entries)
            const activeTimetable = classVersions.find(t => t.is_active);
            const seed = activeTimetable || classVersions[0] || null;

            // Generate default periods for new timetable (use seed's period structure but not entries)
            const periods = seed?.periods?.length > 0 
                ? this.convertSeedPeriods(seed.periods)
                : this.generateDefaultPeriods(defaultStartTime, defaultMinutesPerPeriod, defaultBreakDuration, defaultPeriodCount);

            // New timetable starts with empty entries (don't copy from seed)
            const entries = {};

            // Add new timetable
            this.timetables.push({
                local_id: this.generateUUID(),
                display_label: cls.label,
                batch_id: cls.batch_id,
                grade_id: cls.grade_id,
                class_id: cls.id,
                version: nextVersion,
                status: 'draft',
                is_active: false,
                week_days: seed?.week_days ?? [...defaultWeekDays],
                school_start_time: seed?.school_start_time ?? defaultStartTime,
                school_end_time: seed?.school_end_time ?? defaultEndTime,
                minutes_per_period: seed?.minutes_per_period ?? defaultMinutesPerPeriod,
                break_duration: seed?.break_duration ?? defaultBreakDuration,
                periods,
                subjects,
                entries,
                isOpen: true,
                isDirty: false,
                isNew: true,
                isExisting: false,
                lastChange: null,
            });

            this.selection.class_id = '';
        },

        convertSeedPeriods(seedPeriods) {
            // Group by period_number and get unique periods
            const periodMap = new Map();
            seedPeriods.forEach(p => {
                if (!periodMap.has(p.period_number)) {
                    // Extract H:i format from various possible formats
                    const extractTime = (timeStr) => {
                        if (!timeStr) return '08:00';
                        const str = String(timeStr);
                        // If already H:i format (5 chars like "08:00")
                        if (/^\d{2}:\d{2}$/.test(str)) return str;
                        // If H:i:s format (8 chars like "08:00:00")
                        if (/^\d{2}:\d{2}:\d{2}$/.test(str)) return str.substring(0, 5);
                        // If ISO datetime format (contains T like "2025-01-01T08:00:00")
                        if (str.includes('T')) {
                            const timePart = str.split('T')[1];
                            if (timePart) return timePart.substring(0, 5);
                        }
                        // If datetime format with space (like "2025-01-01 08:00:00")
                        if (str.includes(' ')) {
                            const timePart = str.split(' ')[1];
                            if (timePart) return timePart.substring(0, 5);
                        }
                        // Try to extract any HH:MM pattern
                        const match = str.match(/(\d{2}:\d{2})/);
                        if (match) return match[1];
                        return '08:00';
                    };

                    periodMap.set(p.period_number, {
                        label: p.is_break ? 'Break' : `Period ${p.period_number}`,
                        starts_at: extractTime(p.starts_at),
                        ends_at: extractTime(p.ends_at),
                        is_break: !!p.is_break
                    });
                }
            });
            
            return Array.from(periodMap.values());
        },

        viewTimetable(idx) {
            this.timetables[idx].isOpen = true;
            this.$nextTick(() => {
                document.getElementById('schedulesContainer')?.scrollIntoView({ behavior: 'smooth' });
            });
        },

        closeTimetableEditor(idx) {
            const tt = this.timetables[idx];
            if (tt.isDirty) {
                if (!confirm('{{ __('time_table.You have unsaved changes. Are you sure you want to close?') }}')) {
                    return;
                }
            }
            tt.isOpen = false;
        },

        removeTimetable(idx) {
            const tt = this.timetables[idx];
            if (tt.isDirty) {
                if (!confirm('{{ __('time_table.You have unsaved changes. Are you sure you want to remove this timetable?') }}')) {
                    return;
                }
            }
            this.timetables.splice(idx, 1);
        },

        async deleteExistingTimetable(idx) {
            const tt = this.timetables[idx];
            if (!tt || !tt.id) return;
            
            if (tt.is_active) {
                alert('{{ __('time_table.Cannot delete active timetable. Deactivate it first.') }}');
                return;
            }
            
            if (!confirm('{{ __('time_table.Are you sure you want to delete this timetable? This action cannot be undone.') }}')) {
                return;
            }
            
            try {
                const res = await fetch(`/time-table/${tt.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                });
                
                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    throw new Error(err.message || failedToDeleteMessage);
                }
                
                // Remove from local list
                this.timetables.splice(idx, 1);
                alert('{{ __('time_table.Timetable deleted successfully') }}');
            } catch (e) {
                console.error(e);
                alert(e.message || '{{ __('time_table.Failed to delete timetable') }}');
            }
        },

        // Period management
        addPeriod(ttIdx, type) {
            const tt = this.timetables[ttIdx];
            const lastPeriod = tt.periods[tt.periods.length - 1];
            const lastEndMinutes = this.parseTime(lastPeriod?.ends_at) || 480;
            const breakDur = parseInt(tt.break_duration) || 10;
            const periodDur = parseInt(tt.minutes_per_period) || 60;
            
            // Check if this is period 8 or beyond (after period 7)
            const periodCount = tt.periods.filter(p => !p.is_break).length;
            const isAfterPeriod7 = periodCount >= 8;
            
            // Start immediately at the end time of the previous period
            const newStart = this.minutesToTime(lastEndMinutes);
            // Use the configured period duration or break duration
            const duration = type === 'break' ? breakDur : periodDur;
            const newEnd = this.minutesToTime(lastEndMinutes + duration);
            
            tt.periods.push({
                label: type === 'break' ? breakLabel : `${periodLabel} ${periodCount + 1}`,
                starts_at: newStart,
                ends_at: newEnd,
                is_break: type === 'break'
            });
            
            tt.isDirty = true;
        },

        removePeriod(ttIdx, periodIdx) {
            const tt = this.timetables[ttIdx];
            if (tt.periods.length <= 1) {
                alert('{{ __('time_table.At least one period must remain') }}');
                return;
            }
            
            // Remove entries for this period
            tt.week_days.forEach(day => {
                delete tt.entries[this.cellKey(day, periodIdx)];
            });
            
            // Shift entries for periods after this one
            const newEntries = {};
            Object.keys(tt.entries).forEach(key => {
                const [day, pIdx] = key.split('|');
                const pIdxNum = parseInt(pIdx);
                if (pIdxNum > periodIdx) {
                    newEntries[this.cellKey(day, pIdxNum - 1)] = tt.entries[key];
                } else if (pIdxNum < periodIdx) {
                    newEntries[key] = tt.entries[key];
                }
            });
            tt.entries = newEntries;
            
            tt.periods.splice(periodIdx, 1);
            
            // Renumber non-break periods
            let periodNum = 1;
            tt.periods.forEach(p => {
                if (!p.is_break) {
                    p.label = `Period ${periodNum}`;
                    periodNum++;
                }
            });
            
            tt.isDirty = true;
        },

        editPeriodTime(ttIdx, periodIdx) {
            const tt = this.timetables[ttIdx];
            const period = tt.periods[periodIdx];
            
            this.currentTimetableIdx = ttIdx;
            this.currentPeriodIdx = periodIdx;
            this.periodEditData = {
                label: period.label,
                starts_at: period.starts_at,
                ends_at: period.ends_at,
                is_break: period.is_break
            };
            this.periodEditModalOpen = true;
        },

        savePeriodEdit() {
            if (this.currentTimetableIdx === null || this.currentPeriodIdx === null) return;
            
            const tt = this.timetables[this.currentTimetableIdx];
            const period = tt.periods[this.currentPeriodIdx];
            
            period.label = this.periodEditData.label;
            period.starts_at = this.periodEditData.starts_at;
            period.ends_at = this.periodEditData.ends_at;
            period.is_break = this.periodEditData.is_break;
            
            // If changed to break, clear entries for this period
            if (period.is_break) {
                tt.week_days.forEach(day => {
                    delete tt.entries[this.cellKey(day, this.currentPeriodIdx)];
                });
            }
            
            tt.isDirty = true;
            this.periodEditModalOpen = false;
        },

        // Settings modal
        openSettingsModal(ttIdx) {
            const tt = this.timetables[ttIdx];
            this.currentTimetableIdx = ttIdx;
            this.settingsData = {
                school_start_time: tt.school_start_time,
                school_end_time: tt.school_end_time,
                minutes_per_period: tt.minutes_per_period,
                break_duration: tt.break_duration,
                week_days: [...tt.week_days]
            };
            this.settingsModalOpen = true;
        },

        toggleDay(day) {
            const idx = this.settingsData.week_days.indexOf(day);
            if (idx > -1) {
                this.settingsData.week_days.splice(idx, 1);
            } else {
                // Insert in correct order
                const allDaysOrder = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
                this.settingsData.week_days.push(day);
                this.settingsData.week_days.sort((a, b) => allDaysOrder.indexOf(a) - allDaysOrder.indexOf(b));
            }
        },

        applySettings() {
            if (this.currentTimetableIdx === null) return;
            
            const tt = this.timetables[this.currentTimetableIdx];
            tt.school_start_time = this.settingsData.school_start_time;
            tt.school_end_time = this.settingsData.school_end_time;
            tt.minutes_per_period = this.settingsData.minutes_per_period;
            tt.break_duration = this.settingsData.break_duration;
            tt.week_days = [...this.settingsData.week_days];
            tt.isDirty = true;
            
            this.settingsModalOpen = false;
        },

        // Subject modal
        openSubjectModal(ttIdx, day, periodIdx) {
            this.currentTimetableIdx = ttIdx;
            this.currentDay = day;
            this.currentPeriodIdx = periodIdx;
            this.subjectSearch = '';
            this.subjectModalOpen = true;
        },

        get filteredSubjects() {
            if (this.currentTimetableIdx === null) return [];
            const tt = this.timetables[this.currentTimetableIdx];
            const search = this.subjectSearch.toLowerCase();
            
            return (tt.subjects || []).filter(s => {
                if (!search) return true;
                return s.name?.toLowerCase().includes(search) || 
                       s.teacher_name?.toLowerCase().includes(search);
            });
        },

        assignSubject(subject) {
            if (this.currentTimetableIdx === null) return;
            
            const tt = this.timetables[this.currentTimetableIdx];
            const key = this.cellKey(this.currentDay, this.currentPeriodIdx);
            
            tt.entries[key] = {
                subject_id: subject.id,
                teacher_profile_id: subject.teacher_profile_id || '',
                subject_name: subject.name,
                teacher_name: subject.teacher_name || ''
            };
            
            tt.isDirty = true;
            this.subjectModalOpen = false;
        },

        clearSlot() {
            if (this.currentTimetableIdx === null) return;
            
            const tt = this.timetables[this.currentTimetableIdx];
            const key = this.cellKey(this.currentDay, this.currentPeriodIdx);
            delete tt.entries[key];
            
            tt.isDirty = true;
            this.subjectModalOpen = false;
        },

        // Helper functions
        cellKey(day, periodIdx) {
            return `${day}|${periodIdx}`;
        },

        hasEntry(tt, day, periodIdx) {
            return !!tt.entries[this.cellKey(day, periodIdx)];
        },

        getEntrySubject(tt, day, periodIdx) {
            const entry = tt.entries[this.cellKey(day, periodIdx)];
            if (!entry) return '';
            
            // Try to get from entry cache first
            if (entry.subject_name) return entry.subject_name;
            
            // Otherwise look up from subjects
            const subject = (tt.subjects || []).find(s => s.id === entry.subject_id);
            return subject?.name || '';
        },

        getEntryTeacher(tt, day, periodIdx) {
            const entry = tt.entries[this.cellKey(day, periodIdx)];
            if (!entry) return '';
            
            if (entry.teacher_name) return entry.teacher_name;
            
            const subject = (tt.subjects || []).find(s => s.id === entry.subject_id);
            return subject?.teacher_name || '';
        },

        dayLabel(day) {
            const map = { mon: 'Mon', tue: 'Tue', wed: 'Wed', thu: 'Thu', fri: 'Fri', sat: 'Sat', sun: 'Sun' };
            return map[day] || day;
        },

        statusLabel(tt) {
            return tt.is_active ? '{{ __('time_table.Active') }}' : '{{ __('time_table.Inactive') }}';
        },

        statusBadgeClass(tt) {
            return tt.is_active
                ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200'
                : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400';
        },

        // Submit timetable
        async submitTimetable(idx) {
            const tt = this.timetables[idx];
            if (!tt) return;
            
            // Check if any periods have entries
            const hasEntries = Object.keys(tt.entries).length > 0;
            if (!hasEntries) {
                if (!confirm('{{ __('time_table.No subjects assigned. Are you sure you want to save?') }}')) {
                    return;
                }
            }

            // Build periods array for API
            const periods = [];
            tt.periods.forEach((period, pIdx) => {
                tt.week_days.forEach(day => {
                    const entry = tt.entries[this.cellKey(day, pIdx)] || {};
                    periods.push({
                        day_of_week: day,
                        period_number: pIdx + 1,
                        starts_at: period.starts_at,
                        ends_at: period.ends_at,
                        is_break: period.is_break ? 1 : 0,
                        subject_id: entry.subject_id || null,
                        teacher_profile_id: entry.teacher_profile_id || null,
                    });
                });
            });

            const payload = {
                batch_id: tt.batch_id,
                grade_id: tt.grade_id,
                class_id: tt.class_id,
                week_days: tt.week_days,
                school_start_time: tt.school_start_time,
                school_end_time: tt.school_end_time,
                minutes_per_period: tt.minutes_per_period,
                break_duration: tt.break_duration,
                periods,
            };

            const isUpdate = Boolean(mergedConfig.updateUrl);
            const endpoint = isUpdate ? mergedConfig.updateUrl : mergedConfig.storeUrl;
            const method = isUpdate ? 'PUT' : 'POST';
            const body = isUpdate ? payload : { timetables: [payload] };

            try {
                const res = await fetch(endpoint, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(body),
                });

                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    throw new Error(err.message || failedToSaveMessage);
                }

                const result = await res.json().catch(() => ({}));

                tt.isDirty = false;
                tt.lastChange = new Date().toLocaleString();
                tt.isOpen = false;
                
                alert('{{ __('time_table.Timetable saved successfully') }}');
                
                return result;
            } catch (e) {
                console.error(e);
                alert(e.message || '{{ __('time_table.Failed to save timetable') }}');
                return null;
            }
        },

        async submitAndSetActive(idx) {
            const tt = this.timetables[idx];
            if (!tt) return;

            // First save the timetable
            const result = await this.submitTimetable(idx);
            if (!result) return;

            // Then set it as active via API
            try {
                const timetableId = result.timetable_id || result.id;
                if (!timetableId) {
                    // Reload page to get the new timetable
                    window.location.reload();
                    return;
                }

                const setActiveRes = await fetch(`/time-table/${timetableId}/set-active`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                });

                if (!setActiveRes.ok) {
                    throw new Error('Failed to set as active');
                }

                alert('{{ __('time_table.Timetable saved and set as active') }}');
                window.location.reload();
            } catch (e) {
                console.error(e);
                alert('{{ __('time_table.Timetable saved but failed to set as active. Please set it manually.') }}');
            }
        },
    };
}
</script>
