/**
 * Exam Schedule Manager
 * Handles dynamic subject loading, ordering, and two-row layout for exam schedules
 */

class ExamScheduleManager {
    constructor(options) {
        this.gradeId = options.gradeId || null;
        this.allSubjects = options.allSubjects || [];
        this.schedules = options.schedules || [];
        this.onSchedulesChange = options.onSchedulesChange || (() => {});
    }

    /**
     * Get subjects available for the current grade
     */
    getAvailableSubjects() {
        if (!this.gradeId) return [];
        return this.allSubjects.filter(s => s.grade_ids.includes(this.gradeId));
    }

    /**
     * Get subjects that haven't been added yet
     */
    getUnaddedSubjects() {
        const availableSubjects = this.getAvailableSubjects();
        const addedSubjectIds = this.schedules.map(s => s.subject_id);
        return availableSubjects.filter(s => !addedSubjectIds.includes(s.id));
    }

    /**
     * Add all unadded subjects with default values
     */
    addAllSubjects(startDate) {
        const unaddedSubjects = this.getUnaddedSubjects();
        
        if (unaddedSubjects.length === 0) {
            return { success: false, message: 'All subjects have already been added' };
        }

        if (!startDate) {
            return { success: false, message: 'Please select a start date first' };
        }

        unaddedSubjects.forEach((subject, index) => {
            const lastSchedule = this.schedules[this.schedules.length - 1];
            
            // For first subject, use start date; for others, use previous schedule
            if (this.schedules.length === 0) {
                // First subject - use start date
                const newSchedule = {
                    subject_id: subject.id,
                    room_id: '',
                    teacher_id: '',
                    exam_date: startDate,
                    start_time: '',
                    end_time: '',
                    total_marks: 100,
                    passing_marks: 40,
                    order: 1
                };
                this.schedules.push(newSchedule);
            } else {
                // Subsequent subjects - add one day from previous
                const newSchedule = this.createScheduleFromDefaults(subject.id, lastSchedule);
                newSchedule.order = this.schedules.length + 1;
                this.schedules.push(newSchedule);
            }
        });

        this.reorderSchedules();
        this.onSchedulesChange(this.schedules);
        
        return { success: true, message: `Added ${unaddedSubjects.length} subject(s)` };
    }

    /**
     * Create a schedule with default values based on previous schedule
     */
    createScheduleFromDefaults(subjectId, previousSchedule) {
        const schedule = {
            subject_id: subjectId,
            room_id: '',
            teacher_id: '',
            exam_date: '',
            start_time: '',
            end_time: '',
            total_marks: 100,
            passing_marks: 40,
            order: 1
        };

        if (previousSchedule) {
            // Copy time from previous
            schedule.start_time = previousSchedule.start_time || '';
            schedule.end_time = previousSchedule.end_time || '';
            
            // Set date to next day of previous
            if (previousSchedule.exam_date) {
                const prevDate = new Date(previousSchedule.exam_date);
                prevDate.setDate(prevDate.getDate() + 1);
                schedule.exam_date = prevDate.toISOString().split('T')[0];
            }
        }

        return schedule;
    }

    /**
     * Remove a schedule by index
     */
    removeSchedule(index) {
        this.schedules.splice(index, 1);
        this.reorderSchedules();
        this.onSchedulesChange(this.schedules);
    }

    /**
     * Update schedule order
     */
    updateOrder(index, newOrder) {
        const order = parseInt(newOrder);
        if (isNaN(order) || order < 1 || order > this.schedules.length) {
            return false;
        }

        const schedule = this.schedules[index];
        const oldOrder = schedule.order;

        if (oldOrder === order) return true;

        // Remove from current position
        this.schedules.splice(index, 1);
        
        // Insert at new position (order - 1 because array is 0-indexed)
        this.schedules.splice(order - 1, 0, schedule);
        
        // Reorder all
        this.reorderSchedules();
        this.onSchedulesChange(this.schedules);
        
        return true;
    }

    /**
     * Reorder all schedules sequentially
     */
    reorderSchedules() {
        this.schedules.forEach((schedule, index) => {
            schedule.order = index + 1;
        });
    }

    /**
     * Update grade and reset schedules
     */
    setGrade(gradeId) {
        this.gradeId = gradeId;
        this.schedules = [];
        this.onSchedulesChange(this.schedules);
    }

    /**
     * Get subject name by ID
     */
    getSubjectName(subjectId) {
        const subject = this.allSubjects.find(s => s.id === subjectId);
        return subject ? subject.name : '';
    }
}

// Export for use in Alpine.js or vanilla JS
if (typeof window !== 'undefined') {
    window.ExamScheduleManager = ExamScheduleManager;
}
