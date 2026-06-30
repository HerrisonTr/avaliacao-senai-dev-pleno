import { auth } from '../../auth.js';
import { escapeHtml, toast } from '../../ui.js';
import { http } from '../../http.js';
import { scheduleService } from '../../services/scheduleService.js';
import { userService } from '../../services/userService.js';
import { initAppointmentCreate } from './agendamentos.create.js';
import { initAppointmentEdit } from './agendamentos.edit.js';
import { appointmentStatusLabels, getStatusBadgeClass } from './agendamentos.form.js';
import { initAppointmentStatus } from './agendamentos.status.js';

await auth.protectPage();

const createAppointmentButton = document.querySelector('#create-appointment-button');
const appointmentsTableBody = document.querySelector('#appointments-table-body');
const viewAppointmentModalElement = document.querySelector('#modal-visualizacao-agendamento');
const viewAppointmentModal = bootstrap.Modal.getOrCreateInstance(viewAppointmentModalElement);
const appointmentsCalendarElement = document.querySelector('#appointments-calendar');

const canListAppointments = auth.hasPermission('appointment.list');
const canCreateAppointment = auth.hasPermission('appointment.create');
const canUpdateAppointment = auth.hasPermission('appointment.update');

let appointmentsCache = [];
let appointmentsCalendar = null;

function formatDate(date) {
    if (!date) {
        return '-';
    }

    const [year, month, day] = String(date).split('-');
    return `${day}/${month}/${year}`;
}

function canManageAppointment(appointment) {
    return canUpdateAppointment && appointment.status === 'scheduled';
}

function getStatusEventColor(status) {
    if (status === 'completed') {
        return '#198754';
    }

    if (status === 'cancelled') {
        return '#dc3545';
    }

    return '#0d6efd';
}

function formatTimeRange(startTime, endTime) {
    if (!startTime || !endTime) {
        return '-';
    }

    return `${startTime} às ${endTime}`;
}

function buildCalendarEvents(appointments) {
    return appointments
        .filter((appointment) => appointment.appointment_date && appointment.start_time && appointment.end_time)
        .map((appointment) => ({
            id: String(appointment.id),
            title: `${appointment.customer_name ?? 'Sem nome'} • ${appointment.service?.name ?? 'Serviço'}`,
            start: `${appointment.appointment_date}T${appointment.start_time}`,
            end: `${appointment.appointment_date}T${appointment.end_time}`,
            backgroundColor: getStatusEventColor(appointment.status),
            borderColor: getStatusEventColor(appointment.status),
            extendedProps: {
                appointmentId: appointment.id,
            },
        }));
}

function fillViewAppointmentModal(appointment) {
    document.querySelector('#appointment-view-customer-name').value = appointment.customer_name ?? '';
    document.querySelector('#appointment-view-status').value = appointmentStatusLabels[appointment.status] ?? appointment.status ?? '';
    document.querySelector('#appointment-view-attendant').value = appointment.attendant?.name ?? '';
    document.querySelector('#appointment-view-service').value = appointment.service?.name ?? '';
    document.querySelector('#appointment-view-date').value = formatDate(appointment.appointment_date);
    document.querySelector('#appointment-view-time').value = formatTimeRange(appointment.start_time, appointment.end_time);
    document.querySelector('#appointment-view-customer-phone').value = appointment.customer_phone ?? '';
    document.querySelector('#appointment-view-customer-email').value = appointment.customer_email ?? '';
}

function openViewAppointmentModal(appointment) {
    fillViewAppointmentModal(appointment);
    viewAppointmentModal.show();
}

function initAppointmentsCalendar() {
    if (!appointmentsCalendarElement || typeof FullCalendar === 'undefined') {
        return;
    }

    appointmentsCalendar = new FullCalendar.Calendar(appointmentsCalendarElement, {
        locale: 'pt-br',
        initialView: 'dayGridMonth',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            day: 'Dia',
        },
        noEventsContent: 'Nenhum agendamento cadastrado.',
        events: [],
        eventClick(info) {
            const appointment = getAppointmentById(info.event.extendedProps.appointmentId);

            if (!appointment) {
                return;
            }

            openViewAppointmentModal(appointment);
        },
    });

    appointmentsCalendar.render();
}

function refreshAppointmentsCalendar(appointments) {
    if (!appointmentsCalendar) {
        return;
    }

    appointmentsCalendar.removeAllEvents();
    appointmentsCalendar.addEventSource(buildCalendarEvents(appointments));
}

function buildActionButtons(appointment) {
    const buttons = [
        `
            <button type="button" class="btn btn-primary btn-sm" title="Visualizar" data-action="view" data-appointment-id="${appointment.id}">
                <i class="bi bi-eye"></i>
            </button>
        `,
    ];

    if (canManageAppointment(appointment)) {
        buttons.push(`
            <button type="button" class="btn btn-warning btn-sm" title="Editar" data-action="edit" data-appointment-id="${appointment.id}">
                <i class="bi bi-pencil-square"></i>
            </button>
        `);
        buttons.push(`
            <button type="button" class="btn btn-secondary btn-sm" title="Atualizar status" data-action="status" data-appointment-id="${appointment.id}">
                <i class="bi bi-arrow-repeat"></i>
            </button>
        `);
    }

    return `<div class="btn-group" role="group" aria-label="Ações do agendamento">${buttons.join('')}</div>`;
}

function renderAppointments(appointments) {
    if (!appointments.length) {
        appointmentsTableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted py-4">Nenhum agendamento cadastrado.</td>
            </tr>
        `;
        return;
    }

    appointmentsTableBody.innerHTML = appointments.map((appointment) => `
        <tr>
            <td>${escapeHtml(appointment.customer_name ?? '-')}</td>
            <td>${escapeHtml(appointment.attendant?.name ?? '-')}</td>
            <td>${escapeHtml(appointment.service?.name ?? '-')}</td>
            <td>${escapeHtml(formatDate(appointment.appointment_date))}</td>
            <td>${escapeHtml(formatTimeRange(appointment.start_time, appointment.end_time))}</td>
            <td><span class="badge ${getStatusBadgeClass(appointment.status)}">${escapeHtml(appointmentStatusLabels[appointment.status] ?? appointment.status ?? '-')}</span></td>
            <td class="text-center">${buildActionButtons(appointment)}</td>
        </tr>
    `).join('');
}

async function loadAppointments() {
    if (!canListAppointments) {
        appointmentsTableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-danger py-4">Você não tem permissão para listar agendamentos.</td>
            </tr>
        `;
        return;
    }

    appointmentsTableBody.innerHTML = `
        <tr>
            <td colspan="7" class="text-center text-muted py-4">Carregando agendamentos...</td>
        </tr>
    `;

    try {
        const response = await scheduleService.list();
        appointmentsCache = response.data ?? [];
        renderAppointments(appointmentsCache);
        refreshAppointmentsCalendar(appointmentsCache);
    } catch (error) {
        appointmentsTableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-danger py-4">Não foi possível carregar os agendamentos.</td>
            </tr>
        `;
        toast({
            icon: 'error',
            title: 'Erro ao listar agendamentos',
            text: error.message || 'Não foi possível carregar os agendamentos.',
        });
    }
}

function getAppointmentById(appointmentId) {
    return appointmentsCache.find((appointment) => Number(appointment.id) === Number(appointmentId)) ?? null;
}

const appointmentCreateController = initAppointmentCreate({
    canCreateAppointment,
    onSuccess: loadAppointments,
});

const appointmentEditController = initAppointmentEdit({
    onSuccess: loadAppointments,
});

const appointmentStatusController = initAppointmentStatus({
    onSuccess: loadAppointments,
});

async function loadAttendants() {
    if (!auth.hasPermission('user.list')) {
        return;
    }

    try {
        const response = await userService.list();
        const attendants = (response.data ?? [])
            .filter((user) => user.role === 'Atendente' && user.active);

        const options = `
            <option value="">Selecione um atendente</option>
            ${attendants.map((attendant) => `
                <option value="${attendant.id}">${attendant.name}</option>
            `).join('')}
        `;

        appointmentCreateController.setAttendantOptions(options);
        appointmentEditController.setAttendantOptions(options);
    } catch (error) {
        toast({
            icon: 'error',
            title: 'Erro ao carregar atendentes',
            text: error.message || 'Não foi possível carregar a lista de atendentes.',
        });
    }
}

async function loadServices() {
    if (!auth.hasPermission('service.list')) {
        return;
    }

    try {
        const response = await http.get('/services');
        const services = response.data ?? [];

        const options = `
            <option value="">Selecione um serviço</option>
            ${services.map((service) => `
                <option value="${service.id}">${service.name}</option>
            `).join('')}
        `;

        appointmentCreateController.setServiceOptions(options);
        appointmentEditController.setServiceOptions(options);
    } catch (error) {
        toast({
            icon: 'error',
            title: 'Erro ao carregar serviços',
            text: error.message || 'Não foi possível carregar a lista de serviços.',
        });
    }
}

if (!canCreateAppointment && createAppointmentButton) {
    createAppointmentButton.classList.add('d-none');
}

appointmentsTableBody?.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-action]');

    if (!button) {
        return;
    }

    const { action, appointmentId } = button.dataset;
    const appointment = getAppointmentById(appointmentId);

    if (!appointment) {
        toast({
            icon: 'error',
            title: 'Agendamento não encontrado',
            text: 'Não foi possível localizar os dados do agendamento selecionado.',
        });
        return;
    }

    if (action === 'view') {
        openViewAppointmentModal(appointment);
        return;
    }

    if (!canManageAppointment(appointment)) {
        toast({
            icon: 'warning',
            title: 'Ação não permitida',
            text: appointment.status !== 'scheduled'
                ? 'Apenas agendamentos com status agendado podem ser alterados.'
                : 'Você não tem permissão para alterar este agendamento.',
        });
        return;
    }

    if (action === 'edit') {
        await appointmentEditController.openEditAppointmentModal(appointment);
        return;
    }

    if (action === 'status') {
        appointmentStatusController.openStatusAppointmentModal(appointment);
    }
});

initAppointmentsCalendar();
await loadAttendants();
await loadServices();
await loadAppointments();
