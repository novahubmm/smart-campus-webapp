// Wizard.js - Reusable Pure JavaScript Wizard Component
// Usage: new Wizard('#wizardForm', { stepSelector: '[wizard-step]', nextBtn: '.wizard-next', prevBtn: '.wizard-prev', submitBtn: '.wizard-submit', onStepChange: function(step) {} })

class Wizard {
    constructor(formSelector, options = {}) {
        this.form = document.querySelector(formSelector);
        this.steps = Array.from(this.form.querySelectorAll(options.stepSelector || '[wizard-step]'));
        this.currentStep = 0;
        this.nextBtnClass = options.nextBtn || '.wizard-next';
        this.prevBtnClass = options.prevBtn || '.wizard-prev';
        this.submitBtnClass = options.submitBtn || '.wizard-submit';
        this.onStepChange = options.onStepChange || null;
        this.init();
    }

    init() {
        this.showStep(this.currentStep);
        this.form.addEventListener('click', (e) => {
            if (e.target.matches(this.nextBtnClass)) {
                e.preventDefault();
                if (this.validateStep(this.currentStep)) {
                    this.showStep(++this.currentStep);
                }
            } else if (e.target.matches(this.prevBtnClass)) {
                e.preventDefault();
                this.showStep(--this.currentStep);
            }
        });
    }

    showStep(index) {
        this.currentStep = index;
        this.steps.forEach((step, i) => {
            step.style.display = i === index ? 'block' : 'none';
        });
        // Call the onStepChange callback if provided (step numbers are 1-indexed for display)
        if (this.onStepChange) {
            this.onStepChange(index + 1);
        }
    }

    goToStep(stepNumber) {
        // stepNumber is 1-indexed (1, 2, 3, 4), convert to 0-indexed
        const index = stepNumber - 1;
        if (index >= 0 && index < this.steps.length) {
            this.showStep(index);
        }
    }

    validateStep(index) {
        const step = this.steps[index];
        let valid = true;
        const inputs = step.querySelectorAll('input, select, textarea');
        step.querySelectorAll('.wizard-error').forEach(e => e.remove());
        inputs.forEach(input => {
            if (input.hasAttribute('required') && !input.value) {
                valid = false;
                const error = document.createElement('div');
                error.className = 'wizard-error text-red-500 text-xs mt-1';
                error.textContent = 'This field is required.';
                input.parentNode.appendChild(error);
            }
        });
        return valid;
    }
}

window.Wizard = Wizard;
