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

const createAvailabilityModalElement = document.querySelector('#modal-cadastro-agendamento');
const createAvailabilityModal = bootstrap.Modal.getOrCreateInstance(createAvailabilityModalElement);
const availabilityForm = document.querySelector('#availability-form');
const availabilityFormSubmit = document.querySelector('#availability-form-submit');
const availabilityActive = document.querySelector('#availability-active');

const availabilityFieldMap = {
    attendant_id: document.querySelector('#availability-attendant'),
    day_of_week: document.querySelector('#availability-weekday'),
    start_time: document.querySelector('#availability-start-time'),
    end_time: document.querySelector('#availability-end-time'),
};

function resetAvailabilityFormState() {
    availabilityForm.reset();
    availabilityActive.checked = true;
    clearFieldErrors(availabilityFieldMap);
    $('#availability-attendant').val('').trigger('change');
    $('#availability-weekday').val('').trigger('change');
    availabilityFieldMap.start_time._flatpickr?.clear();
    availabilityFieldMap.end_time._flatpickr?.clear();
}

function initCreateAvailabilityUi() {
    initSelect2({
        selectors: [
            '#availability-attendant',
            '#availability-weekday',
        ],
        dropdownParent: '#modal-cadastro-agendamento',
    });

    initFlatpickrTime([
        '#availability-start-time',
        '#availability-end-time',
    ]);
}

export function initAvailabilityCreate({ canCreateAvailability, onSuccess }) {
    initCreateAvailabilityUi();

    createAvailabilityModalElement?.addEventListener('show.bs.modal', (event) => {
        if (canCreateAvailability) {
            return;
        }

        event.preventDefault();
        toast({
            icon: 'warning',
            title: 'Ação não permitida',
            text: 'Você não tem permissão para cadastrar disponibilidades.',
        });
    });

    createAvailabilityModalElement?.addEventListener('hidden.bs.modal', () => {
        resetAvailabilityFormState();
    });

    availabilityForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        clearFieldErrors(availabilityFieldMap);

        const payload = getTrimmedFormData(availabilityForm, ['attendant_id', 'day_of_week']);
        payload.active = availabilityActive.checked;

        const validationErrors = validateAvailabilityForm(payload);

        if (Object.keys(validationErrors).length > 0) {
            Object.entries(validationErrors).forEach(([field, message]) => {
                setFieldError(availabilityFieldMap, field, message);
            });
            toast({
                icon: 'warning',
                title: 'Validação',
                text: 'Corrija os campos destacados e tente novamente.',
            });
            return;
        }

        setButtonLoadingState(availabilityFormSubmit, 'Salvando...', true);

        try {
            const response = await availabilityService.create(payload);

            createAvailabilityModal.hide();
            resetAvailabilityFormState();
            await onSuccess();
            toast({
                icon: 'success',
                title: 'Sucesso',
                text: response.message || 'Disponibilidade cadastrada com sucesso.',
            });
        } catch (error) {
            if (error.status === 422) {
                applyValidationErrors(availabilityFieldMap, error.data?.errors);
                toast({
                    icon: 'warning',
                    title: 'Verifique os dados',
                    text: error.message || 'Verifique os dados informados.',
                });
            } else {
                toast({
                    icon: 'error',
                    title: 'Erro ao cadastrar disponibilidade',
                    text: error.message || 'Não foi possível cadastrar a disponibilidade.',
                });
            }
        } finally {
            setButtonLoadingState(availabilityFormSubmit, 'Salvando...', false);
        }
    });

    return {
        setAttendantOptions(options) {
            availabilityFieldMap.attendant_id.innerHTML = options;
            $('#availability-attendant').val('').trigger('change');
        },
    };
}
