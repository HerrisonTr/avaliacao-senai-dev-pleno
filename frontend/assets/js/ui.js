export function toast({
    icon = 'success',
    title = '',
    text = '',
    position = 'top-end',
    timer = 3000,
    timerProgressBar = true,
    showConfirmButton = false,
    toast = true,
    didOpen,
} = {}) {
    return Swal.fire({
        icon,
        title,
        text,
        position,
        timer,
        timerProgressBar,
        showConfirmButton,
        toast,
        didOpen: didOpen ?? ((element) => {
            element.addEventListener('mouseenter', Swal.stopTimer);
            element.addEventListener('mouseleave', Swal.resumeTimer);
        }),
    });
}

export function confirmDialog({
    title = 'Confirma esta ação?',
    text = '',
    icon = 'warning',
    confirmButtonText = 'Confirmar',
    cancelButtonText = 'Cancelar',
    confirmButtonColor = '#dc3545',
    cancelButtonColor = '#6c757d',
    reverseButtons = true,
    focusCancel = true,
} = {}) {
    return Swal.fire({
        title,
        text,
        icon,
        showCancelButton: true,
        confirmButtonText,
        cancelButtonText,
        confirmButtonColor,
        cancelButtonColor,
        reverseButtons,
        focusCancel,
    });
}

export function initSelect2({
    selectors = [],
    dropdownParent = null,
    placeholder = 'Selecione',
    theme = 'bootstrap-5',
    allowClear = true,
    width = '100%',
} = {}) {
    const dropdownParentElement = dropdownParent ? $(dropdownParent) : undefined;

    selectors.forEach((selector) => {
        $(selector).select2({
            width,
            dropdownParent: dropdownParentElement,
            placeholder,
            theme,
            allowClear,
        });
    });
}

export function initFlatpickrTime(selectors = [], options = {}) {
    const baseOptions = {
        enableTime: true,
        noCalendar: true,
        dateFormat: 'H:i',
        altFormat: 'H:i',
        time_24hr: true,
        minuteIncrement: 15,
        allowInput: false,
        clickOpens: true,
        altInput: true,
        altInputClass: 'form-control',
    };

    selectors.forEach((selector) => {
        flatpickr(selector, {
            ...baseOptions,
            ...options,
        });
    });
}

export function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}
