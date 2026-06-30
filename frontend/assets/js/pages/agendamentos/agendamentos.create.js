import { initSelect2, toast } from '../../ui.js';
import {
    applyValidationErrors,
    clearFieldErrors,
    getTrimmedFormData,
    setButtonLoadingState,
    setFieldError,
} from '../../ui.form.js';
import { scheduleService } from '../../services/scheduleService.js';
import {
    buildTimeOptions,
    formatOccupiedTimes,
    isAvailablePeriod,
    validateAppointmentForm,
} from './agendamentos.form.js';

const createAppointmentModalElement = document.querySelector('#modal-cadastro-agendamento');
const createAppointmentModal = bootstrap.Modal.getOrCreateInstance(createAppointmentModalElement);
const appointmentForm = document.querySelector('#appointment-form');
const appointmentFormSubmit = document.querySelector('#appointment-form-submit');
const occupiedTimesAlert = document.querySelector('#appointment-occupied-times');

const appointmentFieldMap = {
    attendant_id: document.querySelector('#appointment-attendant'),
    service_id: document.querySelector('#appointment-service'),
    appointment_date: document.querySelector('#appointment-date'),
    start_time: document.querySelector('#appointment-start-time'),
    end_time: document.querySelector('#appointment-end-time'),
    customer_name: document.querySelector('#appointment-customer-name'),
    customer_phone: document.querySelector('#appointment-customer-phone'),
    customer_email: document.querySelector('#appointment-customer-email'),
};

let availableSlots = [];

function initCreateAppointmentUi() {
    initSelect2({
        selectors: [
            '#appointment-attendant',
            '#appointment-service',
            '#appointment-start-time',
            '#appointment-end-time',
        ],
        dropdownParent: '#modal-cadastro-agendamento',
    });
}

function resetAvailableTimesState() {
    availableSlots = [];
    appointmentFieldMap.start_time.innerHTML = '<option value="">Selecione um horário</option>';
    appointmentFieldMap.end_time.innerHTML = '<option value="">Selecione um horário</option>';
    $('#appointment-start-time').val('').trigger('change');
    $('#appointment-end-time').val('').trigger('change');
    occupiedTimesAlert.classList.add('d-none');
    occupiedTimesAlert.textContent = '';
}

async function loadAvailableTimes() {
    const attendantId = appointmentFieldMap.attendant_id.value;
    const appointmentDate = appointmentFieldMap.appointment_date.value;

    resetAvailableTimesState();

    if (!attendantId || !appointmentDate) {
        return;
    }

    try {
        const response = await scheduleService.availableTimes({
            attendant_id: attendantId,
            appointment_date: appointmentDate,
        });

        const available = response.data?.available ?? [];
        const occupied = response.data?.occupied ?? [];
        const { options, slots } = buildTimeOptions(available);

        availableSlots = slots;
        appointmentFieldMap.start_time.innerHTML = options;
        appointmentFieldMap.end_time.innerHTML = options;
        $('#appointment-start-time').val('').trigger('change');
        $('#appointment-end-time').val('').trigger('change');

        const occupiedMessage = formatOccupiedTimes(occupied);
        if (occupiedMessage) {
            occupiedTimesAlert.textContent = occupiedMessage;
            occupiedTimesAlert.classList.remove('d-none');
        }

        if (!available.length) {
            toast({
                icon: 'info',
                title: 'Sem horários disponíveis',
                text: 'Não há horários livres para este atendente nesta data.',
            });
        }
    } catch (error) {
        toast({
            icon: 'error',
            title: 'Erro ao carregar horários',
            text: error.message || 'Não foi possível carregar os horários disponíveis.',
        });
    }
}

function resetAppointmentFormState() {
    appointmentForm.reset();
    clearFieldErrors(appointmentFieldMap);
    $('#appointment-attendant').val('').trigger('change');
    $('#appointment-service').val('').trigger('change');
    resetAvailableTimesState();
}

export function initAppointmentCreate({ canCreateAppointment, onSuccess }) {
    initCreateAppointmentUi();

    createAppointmentModalElement?.addEventListener('show.bs.modal', (event) => {
        if (canCreateAppointment) {
            return;
        }

        event.preventDefault();
        toast({
            icon: 'warning',
            title: 'Ação não permitida',
            text: 'Você não tem permissão para cadastrar agendamentos.',
        });
    });

    createAppointmentModalElement?.addEventListener('hidden.bs.modal', () => {
        resetAppointmentFormState();
    });

    appointmentFieldMap.attendant_id?.addEventListener('change', loadAvailableTimes);
    appointmentFieldMap.appointment_date?.addEventListener('change', loadAvailableTimes);

    appointmentForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        clearFieldErrors(appointmentFieldMap);

        const payload = getTrimmedFormData(appointmentForm, ['attendant_id', 'service_id']);

        const validationErrors = validateAppointmentForm(payload);

        if (
            Object.keys(validationErrors).length === 0
            && !isAvailablePeriod(availableSlots, payload.start_time, payload.end_time)
        ) {
            validationErrors.end_time = 'Selecione um intervalo disponível para o agendamento.';
        }

        if (Object.keys(validationErrors).length > 0) {
            Object.entries(validationErrors).forEach(([field, message]) => {
                setFieldError(appointmentFieldMap, field, message);
            });
            toast({
                icon: 'warning',
                title: 'Validação',
                text: 'Corrija os campos destacados e tente novamente.',
            });
            return;
        }

        setButtonLoadingState(appointmentFormSubmit, 'Salvando...', true);

        try {
            const response = await scheduleService.create(payload);

            createAppointmentModal.hide();
            resetAppointmentFormState();
            await onSuccess();
            toast({
                icon: 'success',
                title: 'Sucesso',
                text: response.message || 'Agendamento cadastrado com sucesso.',
            });
        } catch (error) {
            if (error.status === 422) {
                applyValidationErrors(appointmentFieldMap, error.data?.errors);
                toast({
                    icon: 'warning',
                    title: 'Verifique os dados',
                    text: error.message || 'Verifique os dados informados.',
                });
            } else {
                toast({
                    icon: 'error',
                    title: 'Erro ao cadastrar agendamento',
                    text: error.message || 'Não foi possível cadastrar o agendamento.',
                });
            }
        } finally {
            setButtonLoadingState(appointmentFormSubmit, 'Salvando...', false);
        }
    });

    return {
        setAttendantOptions(options) {
            appointmentFieldMap.attendant_id.innerHTML = options;
            $('#appointment-attendant').val('').trigger('change');
        },
        setServiceOptions(options) {
            appointmentFieldMap.service_id.innerHTML = options;
            $('#appointment-service').val('').trigger('change');
        },
    };
}
