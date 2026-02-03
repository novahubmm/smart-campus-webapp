// Academic Management JavaScript - Matching blade_prototype functionality

// Modal functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
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