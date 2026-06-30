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

const editAppointmentModalElement = document.querySelector('#modal-edicao-agendamento');
const editAppointmentModal = bootstrap.Modal.getOrCreateInstance(editAppointmentModalElement);
const appointmentFormEdit = document.querySelector('#appointment-form-edit');
const appointmentFormEditSubmit = document.querySelector('#appointment-form-edit-submit');
const appointmentEditId = document.querySelector('#appointment-edit-id');
const occupiedTimesAlertEdit = document.querySelector('#appointment-edit-occupied-times');

const appointmentEditFieldMap = {
    attendant_id: document.querySelector('#appointment-edit-attendant'),
    service_id: document.querySelector('#appointment-edit-service'),
    appointment_date: document.querySelector('#appointment-edit-date'),
    start_time: document.querySelector('#appointment-edit-start-time'),
    end_time: document.querySelector('#appointment-edit-end-time'),
    customer_name: document.querySelector('#appointment-edit-customer-name'),
    customer_phone: document.querySelector('#appointment-edit-customer-phone'),
    customer_email: document.querySelector('#appointment-edit-customer-email'),
};

let editAvailableSlots = [];
let currentAppointmentSlot = null;
let isHydratingEditForm = false;

function initEditAppointmentUi() {
    initSelect2({
        selectors: [
            '#appointment-edit-attendant',
            '#appointment-edit-service',
            '#appointment-edit-start-time',
            '#appointment-edit-end-time',
        ],
        dropdownParent: '#modal-edicao-agendamento',
    });
}

function resetAvailableTimesEditState() {
    editAvailableSlots = [];
    currentAppointmentSlot = null;
    appointmentEditFieldMap.start_time.innerHTML = '<option value="">Selecione um horário</option>';
    appointmentEditFieldMap.end_time.innerHTML = '<option value="">Selecione um horário</option>';
    $('#appointment-edit-start-time').val('').trigger('change');
    $('#appointment-edit-end-time').val('').trigger('change');
    occupiedTimesAlertEdit.classList.add('d-none');
    occupiedTimesAlertEdit.textContent = '';
}

async function loadAvailableTimesForEdit() {
    const attendantId = appointmentEditFieldMap.attendant_id.value;
    const appointmentDate = appointmentEditFieldMap.appointment_date.value;

    resetAvailableTimesEditState();

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
        const availableWithCurrentSlot = currentAppointmentSlot?.start_time && currentAppointmentSlot?.end_time
            ? [...available, currentAppointmentSlot]
            : available;
        const { options, slots } = buildTimeOptions(availableWithCurrentSlot, currentAppointmentSlot);

        editAvailableSlots = slots;
        appointmentEditFieldMap.start_time.innerHTML = options;
        appointmentEditFieldMap.end_time.innerHTML = options;
        $('#appointment-edit-start-time').val(currentAppointmentSlot?.start_time ?? '').trigger('change');
        $('#appointment-edit-end-time').val(currentAppointmentSlot?.end_time ?? '').trigger('change');

        const occupiedMessage = formatOccupiedTimes(occupied);
        if (occupiedMessage) {
            occupiedTimesAlertEdit.textContent = occupiedMessage;
            occupiedTimesAlertEdit.classList.remove('d-none');
        }
    } catch (error) {
        toast({
            icon: 'error',
            title: 'Erro ao carregar horários',
            text: error.message || 'Não foi possível carregar os horários disponíveis.',
        });
    }
}

function resetAppointmentEditFormState() {
    appointmentFormEdit.reset();
    appointmentEditId.value = '';
    clearFieldErrors(appointmentEditFieldMap);
    $('#appointment-edit-attendant').val('').trigger('change');
    $('#appointment-edit-service').val('').trigger('change');
    resetAvailableTimesEditState();
}

async function openEditAppointmentModal(appointment) {
    isHydratingEditForm = true;
    appointmentEditId.value = appointment.id;
    appointmentEditFieldMap.customer_name.value = appointment.customer_name ?? '';
    appointmentEditFieldMap.customer_phone.value = appointment.customer_phone ?? '';
    appointmentEditFieldMap.customer_email.value = appointment.customer_email ?? '';
    appointmentEditFieldMap.appointment_date.value = appointment.appointment_date ?? '';

    currentAppointmentSlot = {
        start_time: appointment.start_time ?? '',
        end_time: appointment.end_time ?? '',
    };

    $('#appointment-edit-attendant').val(String(appointment.attendant_id ?? appointment.attendant?.id ?? '')).trigger('change');
    $('#appointment-edit-service').val(String(appointment.service_id ?? appointment.service?.id ?? '')).trigger('change');

    await loadAvailableTimesForEdit();
    isHydratingEditForm = false;
    clearFieldErrors(appointmentEditFieldMap);
    editAppointmentModal.show();
}

export function initAppointmentEdit({ onSuccess }) {
    initEditAppointmentUi();

    editAppointmentModalElement?.addEventListener('hidden.bs.modal', () => {
        isHydratingEditForm = false;
        resetAppointmentEditFormState();
    });

    appointmentEditFieldMap.attendant_id?.addEventListener('change', async () => {
        if (isHydratingEditForm) {
            return;
        }

        if (currentAppointmentSlot) {
            currentAppointmentSlot = {
                start_time: appointmentEditFieldMap.start_time.value || currentAppointmentSlot.start_time,
                end_time: appointmentEditFieldMap.end_time.value || currentAppointmentSlot.end_time,
            };
        }

        await loadAvailableTimesForEdit();
    });

    appointmentEditFieldMap.appointment_date?.addEventListener('change', async () => {
        if (isHydratingEditForm) {
            return;
        }

        if (currentAppointmentSlot) {
            currentAppointmentSlot = {
                start_time: appointmentEditFieldMap.start_time.value || currentAppointmentSlot.start_time,
                end_time: appointmentEditFieldMap.end_time.value || currentAppointmentSlot.end_time,
            };
        }

        await loadAvailableTimesForEdit();
    });

    appointmentFormEdit?.addEventListener('submit', async (event) => {
        event.preventDefault();

        clearFieldErrors(appointmentEditFieldMap);

        const appointmentId = appointmentEditId.value;
        const payload = getTrimmedFormData(appointmentFormEdit, ['attendant_id', 'service_id']);

        const validationErrors = validateAppointmentForm(payload);

        if (
            Object.keys(validationErrors).length === 0
            && !isAvailablePeriod(editAvailableSlots, payload.start_time, payload.end_time)
        ) {
            validationErrors.end_time = 'Selecione um intervalo disponível para o agendamento.';
        }

        if (Object.keys(validationErrors).length > 0) {
            Object.entries(validationErrors).forEach(([field, message]) => {
                setFieldError(appointmentEditFieldMap, field, message);
            });
            toast({
                icon: 'warning',
                title: 'Validação',
                text: 'Corrija os campos destacados e tente novamente.',
            });
            return;
        }

        setButtonLoadingState(appointmentFormEditSubmit, 'Salvando...', true);

        try {
            const response = await scheduleService.update(appointmentId, payload);

            editAppointmentModal.hide();
            resetAppointmentEditFormState();
            await onSuccess();
            toast({
                icon: 'success',
                title: 'Sucesso',
                text: response.message || 'Agendamento atualizado com sucesso.',
            });
        } catch (error) {
            if (error.status === 422) {
                applyValidationErrors(appointmentEditFieldMap, error.data?.errors);
                toast({
                    icon: 'warning',
                    title: 'Verifique os dados',
                    text: error.message || 'Verifique os dados informados.',
                });
            } else {
                toast({
                    icon: 'error',
                    title: 'Erro ao editar agendamento',
                    text: error.message || 'Não foi possível atualizar o agendamento.',
                });
            }
        } finally {
            setButtonLoadingState(appointmentFormEditSubmit, 'Salvando...', false);
        }
    });

    return {
        openEditAppointmentModal,
        setAttendantOptions(options) {
            appointmentEditFieldMap.attendant_id.innerHTML = options;
            $('#appointment-edit-attendant').val('').trigger('change');
        },
        setServiceOptions(options) {
            appointmentEditFieldMap.service_id.innerHTML = options;
            $('#appointment-edit-service').val('').trigger('change');
        },
    };
}
