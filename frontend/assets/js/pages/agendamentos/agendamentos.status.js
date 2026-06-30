import { toast } from '../../ui.js';
import {
    clearFieldErrors,
    setButtonLoadingState,
    setFieldError,
} from '../../ui.form.js';
import { scheduleService } from '../../services/scheduleService.js';
import { appointmentStatusLabels } from './agendamentos.form.js';

const statusAppointmentModalElement = document.querySelector('#modal-status-agendamento');
const statusAppointmentModal = bootstrap.Modal.getOrCreateInstance(statusAppointmentModalElement);
const appointmentStatusForm = document.querySelector('#appointment-status-form');
const appointmentStatusSubmit = document.querySelector('#appointment-status-submit');
const appointmentStatusId = document.querySelector('#appointment-status-id');
const appointmentStatusCustomerName = document.querySelector('#appointment-status-customer-name');
const appointmentStatusCurrent = document.querySelector('#appointment-status-current');

const appointmentStatusFieldMap = {
    status: document.querySelector('#appointment-status-new'),
};

function resetAppointmentStatusFormState() {
    appointmentStatusForm.reset();
    appointmentStatusId.value = '';
    appointmentStatusCustomerName.value = '';
    appointmentStatusCurrent.value = '';
    clearFieldErrors(appointmentStatusFieldMap);
}

function openStatusAppointmentModal(appointment) {
    appointmentStatusId.value = appointment.id;
    appointmentStatusCustomerName.value = appointment.customer_name ?? '';
    appointmentStatusCurrent.value = appointmentStatusLabels[appointment.status] ?? appointment.status ?? '';
    clearFieldErrors(appointmentStatusFieldMap);
    statusAppointmentModal.show();
}

export function initAppointmentStatus({ onSuccess }) {
    statusAppointmentModalElement?.addEventListener('hidden.bs.modal', () => {
        resetAppointmentStatusFormState();
    });

    appointmentStatusForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        clearFieldErrors(appointmentStatusFieldMap);

        const appointmentId = appointmentStatusId.value;
        const status = appointmentStatusFieldMap.status.value;

        if (!status) {
            setFieldError(appointmentStatusFieldMap, 'status', 'Selecione o novo status.');
            toast({
                icon: 'warning',
                title: 'Validação',
                text: 'Selecione o novo status para continuar.',
            });
            return;
        }

        setButtonLoadingState(appointmentStatusSubmit, 'Salvando...', true);

        try {
            const response = await scheduleService.updateStatus(appointmentId, { status });

            statusAppointmentModal.hide();
            resetAppointmentStatusFormState();
            await onSuccess();
            toast({
                icon: 'success',
                title: 'Sucesso',
                text: response.message || 'Status atualizado com sucesso.',
            });
        } catch (error) {
            if (error.status === 422) {
                setFieldError(appointmentStatusFieldMap, 'status', error.message || 'Selecione um status válido.');
                toast({
                    icon: 'warning',
                    title: 'Verifique os dados',
                    text: error.message || 'Verifique os dados informados.',
                });
            } else {
                toast({
                    icon: 'error',
                    title: 'Erro ao atualizar status',
                    text: error.message || 'Não foi possível atualizar o status do agendamento.',
                });
            }
        } finally {
            setButtonLoadingState(appointmentStatusSubmit, 'Salvando...', false);
        }
    });

    return {
        openStatusAppointmentModal,
    };
}
