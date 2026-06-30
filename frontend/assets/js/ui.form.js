export function clearFieldErrors(fieldMap) {
    Object.values(fieldMap).forEach((field) => {
        field.classList.remove('is-invalid');

        if (field._flatpickr?.altInput) {
            field._flatpickr.altInput.classList.remove('is-invalid');
        }

        const select2Selection = field.nextElementSibling?.querySelector('.select2-selection');
        if (select2Selection) {
            select2Selection.classList.remove('is-invalid');
        }

        const feedback = field.parentElement.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.textContent = '';
        }
    });
}

export function setFieldError(fieldMap, fieldName, message) {
    const field = fieldMap[fieldName];

    if (!field) {
        return;
    }

    field.classList.add('is-invalid');

    if (field._flatpickr?.altInput) {
        field._flatpickr.altInput.classList.add('is-invalid');
    }

    const select2Selection = field.nextElementSibling?.querySelector('.select2-selection');
    if (select2Selection) {
        select2Selection.classList.add('is-invalid');
    }

    const feedback = field.parentElement.querySelector('.invalid-feedback');
    if (feedback) {
        feedback.textContent = message;
    }
}

export function applyValidationErrors(fieldMap, errors = {}) {
    Object.entries(errors).forEach(([fieldName, messages]) => {
        setFieldError(fieldMap, fieldName, messages[0]);
    });
}

export function getTrimmedFormData(form, numericFields = []) {
    const formData = new FormData(form);
    const payload = {};

    formData.forEach((value, key) => {
        const normalizedValue = typeof value === 'string' ? value.trim() : value;
        payload[key] = numericFields.includes(key) ? Number(normalizedValue || 0) : normalizedValue;
    });

    return payload;
}

export function setButtonLoadingState(button, loadingText, isLoading) {
    if (!button.dataset.originalText) {
        button.dataset.originalText = button.textContent;
    }

    button.disabled = isLoading;
    button.textContent = isLoading ? loadingText : button.dataset.originalText;
}
