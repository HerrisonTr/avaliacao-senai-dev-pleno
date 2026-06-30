import { initFlatpickrTime, initSelect2, toast } from '../../ui.js';
import {
    applyValidationErrors,
    clearFieldErrors,
    getTrimmedFormData,
    setButtonLoadingState,
    setFieldError,
} from '../../ui.form.js';

import { availabilityService } from '../../services/availabilityService.js';

import { validateAvailabilityForm } from './disponibilidades.form.js';

const editAvailabilityModalElement = document.querySelector('#modal-edicao-agendamento');
const editAvailabilityModal = bootstrap.Modal.getOrCreateInstance(editAvailabilityModalElement);
const availabilityFormEdit = document.querySelector('#availability-form-edit');
const availabilityFormEditSubmit = document.querySelector('#availability-form-edit-submit');
const availabilityEditId = document.querySelector('#availability-edit-id');
const availabilityEditActive = document.querySelector('#availability-edit-active');

const availabilityEditFieldMap = {
    attendant_id: document.querySelector('#availability-edit-attendant'),
    day_of_week: document.querySelector('#availability-edit-weekday'),
    start_time: document.querySelector('#availability-edit-start-time'),
    end_time: document.querySelector('#availability-edit-end-time'),
};

function resetAvailabilityEditFormState() {
    availabilityFormEdit.reset();
    availabilityEditId.value = '';
    availabilityEditActive.checked = false;
    clearFieldErrors(availabilityEditFieldMap);
    $('#availability-edit-attendant').val('').trigger('change');
    $('#availability-edit-weekday').val('').trigger('change');
    availabilityEditFieldMap.start_time._flatpickr?.clear();
    availabilityEditFieldMap.end_time._flatpickr?.clear();
}

function initEditAvailabilityUi() {
    initSelect2({
        selectors: [
            '#availability-edit-attendant',
            '#availability-edit-weekday',
        ],
        dropdownParent: '#modal-edicao-agendamento',
    });

    initFlatpickrTime([
        '#availability-edit-start-time',
        '#availability-edit-end-time',
    ]);
}

function openEditAvailabilityModal(availability) {
    availabilityEditId.value = availability.id;
    availabilityEditActive.checked = Boolean(availability.active);
    clearFieldErrors(availabilityEditFieldMap);
    $('#availability-edit-attendant').val(String(availability.attendant_id ?? availability.attendant?.id ?? '')).trigger('change');
    $('#availability-edit-weekday').val(String(availability.day_of_week ?? '')).trigger('change');
    availabilityEditFieldMap.start_time._flatpickr?.setDate(availability.start_time ?? '', false, 'H:i');
    availabilityEditFieldMap.end_time._flatpickr?.setDate(availability.end_time ?? '', false, 'H:i');
    editAvailabilityModal.show();
}

export function initAvailabilityEdit({ onSuccess }) {
    initEditAvailabilityUi();

    editAvailabilityModalElement?.addEventListener('hidden.bs.modal', () => {
        resetAvailabilityEditFormState();
    });

    availabilityFormEdit?.addEventListener('submit', async (event) => {
        event.preventDefault();

        clearFieldErrors(availabilityEditFieldMap);

        const availabilityId = availabilityEditId.value;
        const payload = getTrimmedFormData(availabilityFormEdit, ['attendant_id', 'day_of_week']);
        payload.active = availabilityEditActive.checked;

        const validationErrors = validateAvailabilityForm(payload);

        if (Object.keys(validationErrors).length > 0) {
            Object.entries(validationErrors).forEach(([field, message]) => {
                setFieldError(availabilityEditFieldMap, field, message);
            });
            toast({
                icon: 'warning',
                title: 'Validação',
                text: 'Corrija os campos destacados e tente novamente.',
            });
            return;
        }

        setButtonLoadingState(availabilityFormEditSubmit, 'Salvando...', true);

        try {
            const response = await availabilityService.update(availabilityId, payload);

            editAvailabilityModal.hide();
            resetAvailabilityEditFormState();
            await onSuccess();
            toast({
                icon: 'success',
                title: 'Sucesso',
                text: response.message || 'Disponibilidade atualizada com sucesso.',
            });
        } catch (error) {
            if (error.status === 422) {
                applyValidationErrors(availabilityEditFieldMap, error.data?.errors);
                toast({
                    icon: 'warning',
                    title: 'Verifique os dados',
                    text: error.message || 'Verifique os dados informados.',
                });
            } else {
                toast({
                    icon: 'error',
                    title: 'Erro ao editar disponibilidade',
                    text: error.message || 'Não foi possível atualizar a disponibilidade.',
                });
            }
        } finally {
            setButtonLoadingState(availabilityFormEditSubmit, 'Salvando...', false);
        }
    });

    return {
        openEditAvailabilityModal,
        setAttendantOptions(options) {
            availabilityEditFieldMap.attendant_id.innerHTML = options;
            $('#availability-edit-attendant').val('').trigger('change');
        },
    };
}
