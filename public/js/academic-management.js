// Academic Management JavaScript - Matching blade_prototype functionality

// Modal functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Reset form when opening
        const form = modal.querySelector('form');
        if (form) {
            // Special handling for batch modal
            if (modalId === 'batchModal') {
                // Use setTimeout to ensure DOM is ready
                setTimeout(() => {
                    // Clear batch name
                    const batchNameInput = form.querySelector('#batchName');
                    if (batchNameInput) {
                        batchNameInput.value = '';
                    }
                    
                    // Set start date to today
                    const startDateInput = form.querySelector('#batchStart');
                    if (startDateInput) {
                        const today = new Date();
                        startDateInput.value = today.toISOString().split('T')[0];
                    }
                    
                    // Set end date to tomorrow
                    const endDateInput = form.querySelector('#batchEnd');
                    if (endDateInput) {
                        const tomorrow = new Date();
                        tomorrow.setDate(tomorrow.getDate() + 1);
                        endDateInput.value = tomorrow.toISOString().split('T')[0];
                    }
                }, 0);
            } else {
                form.reset();
            }
        }
        
        // Initialize Select2 for class modal teacher dropdown
        if (modalId === 'classModal') {
            setTimeout(() => {
                // Check if jQuery and Select2 are available
                if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
                    const teacherSelect = jQuery('#classTeacher');
                    if (teacherSelect.length) {
                        // Destroy existing Select2 if any
                        if (teacherSelect.hasClass('select2-hidden-accessible')) {
                            teacherSelect.select2('destroy');
                        }
                        
                        // Initialize Select2
                        teacherSelect.select2({
                            width: '100%',
                            dropdownParent: jQuery('#classModal'),
                            placeholder: 'Select teacher',
                            allowClear: true
                        });
                    }
                }
            }, 100);
        }
        
        // Initialize Select2 for subject modal grades dropdown
        if (modalId === 'subjectModal') {
            setTimeout(() => {
                // Check if jQuery and Select2 are available
                if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
                    const gradesSelect = jQuery('#createSubjectGrades');
                    if (gradesSelect.length) {
                        // Destroy existing Select2 if any
                        if (gradesSelect.hasClass('select2-hidden-accessible')) {
                            gradesSelect.select2('destroy');
                        }
                        
                        // Initialize Select2
                        gradesSelect.select2({
                            width: '100%',
                            dropdownParent: jQuery('#subjectModal'),
                            placeholder: 'Select grades',
                            allowClear: true
                        });
                    }
                }
            }, 100);
        }
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('simple-modal-overlay')) {
        event.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
});

function deleteBatch(batchId) {
    showConfirmDialog({
        title: 'Delete Batch',
        message: `Are you sure you want to delete batch ${batchId}? This action cannot be undone.`,
        confirmText: 'Delete',
        confirmIcon: 'fas fa-trash',
        onConfirm: function() {
            showActionStatus(`Batch ${batchId} deleted successfully!`, 'success');
        }
    });
}

function deleteGrade(gradeId) {
    showConfirmDialog({
        title: 'Delete Grade',
        message: `Are you sure you want to delete grade ${gradeId}? This will also delete all associated classes and students.`,
        confirmText: 'Delete',
        confirmIcon: 'fas fa-graduation-cap',
        onConfirm: function() {
            showActionStatus(`Grade ${gradeId} deleted successfully!`, 'success');
        }
    });
}

function deleteClass(classId) {
    showConfirmDialog({
        title: 'Delete Class',
        message: `Are you sure you want to delete class ${classId}? This will also remove all students from this class.`,
        confirmText: 'Delete',
        confirmIcon: 'fas fa-door-open',
        onConfirm: function() {
            showActionStatus(`Class ${classId} deleted successfully!`, 'success');
        }
    });
}

function deleteRoom(roomId) {
    showConfirmDialog({
        title: 'Delete Room',
        message: `Are you sure you want to delete room ${roomId}? This will unassign any classes currently using this room.`,
        confirmText: 'Delete',
        confirmIcon: 'fas fa-door-closed',
        onConfirm: function() {
            showActionStatus(`Room ${roomId} deleted successfully!`, 'success');
        }
    });
}


function deleteSubject(subjectId) {
    showConfirmDialog({
        title: 'Delete Subject',
        message: `Are you sure you want to delete subject ${subjectId}? This will remove it from all classes and schedules.`,
        confirmText: 'Delete',
        confirmIcon: 'fas fa-book',
        onConfirm: function() {
            showActionStatus(`Subject ${subjectId} deleted successfully!`, 'success');
        }
    });
}


// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Academic Management JS loaded');
});