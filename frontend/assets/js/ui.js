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

export function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}
