import { auth } from '../../auth.js';
import { escapeHtml, toast } from '../../ui.js';
import { availabilityService } from '../../services/availabilityService.js';
import { userService } from '../../services/userService.js';
import { initAvailabilityCreate } from './disponibilidades.create.js';
import { initAvailabilityEdit } from './disponibilidades.edit.js';
import { weekdayLabels } from './disponibilidades.form.js';

await auth.protectPage();

const createAvailabilityButton = document.querySelector('#create-availability-button');
const availabilityAttendant = document.querySelector('#availability-attendant');
const availabilitiesTableBody = document.querySelector('#availabilities-table-body');

const canCreateAvailability = auth.hasPermission('attendant-availability.create');
const canUpdateAvailability = auth.hasPermission('attendant-availability.update');

let availabilitiesCache = [];

function buildActionButtons(availabilityId) {
    if (!canUpdateAvailability) {
        return '<span class="text-muted">Sem ações disponíveis</span>';
    }

    return `
        <div class="btn-group" role="group" aria-label="Ações da disponibilidade">
            <button type="button" class="btn btn-warning btn-sm" title="Editar" data-action="edit" data-availability-id="${availabilityId}">
                <i class="bi bi-pencil-square"></i>
            </button>
        </div>
    `;
}

function renderAvailabilities(availabilities) {
    if (!availabilities.length) {
        availabilitiesTableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted py-4">Nenhuma disponibilidade cadastrada.</td>
            </tr>
        `;
        return;
    }

    availabilitiesTableBody.innerHTML = availabilities.map((availability) => `
        <tr>
            <td>${escapeHtml(availability.attendant?.name ?? '-')}</td>
            <td>${escapeHtml(weekdayLabels[Number(availability.day_of_week)] ?? '-')}</td>
            <td>${escapeHtml(availability.start_time ?? '-')}</td>
            <td>${escapeHtml(availability.end_time ?? '-')}</td>
            <td>${availability.active ? '<span class="badge text-bg-success">Ativo</span>' : '<span class="badge text-bg-secondary">Inativo</span>'}</td>
            <td class="text-center">${buildActionButtons(availability.id)}</td>
        </tr>
    `).join('');
}

async function loadAvailabilities() {
    availabilitiesTableBody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center text-muted py-4">Carregando disponibilidades...</td>
        </tr>
    `;

    try {
        const response = await availabilityService.list();
        availabilitiesCache = response.data ?? [];
        renderAvailabilities(availabilitiesCache);
    } catch (error) {
        availabilitiesTableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger py-4">Não foi possível carregar as disponibilidades.</td>
            </tr>
        `;
        toast({
            icon: 'error',
            title: 'Erro ao listar disponibilidades',
            text: error.message || 'Não foi possível carregar as disponibilidades.',
        });
    }
}

function getAvailabilityById(availabilityId) {
    return availabilitiesCache.find((availability) => Number(availability.id) === Number(availabilityId)) ?? null;
}

const availabilityCreateController = initAvailabilityCreate({
    canCreateAvailability,
    onSuccess: loadAvailabilities,
});

const availabilityEditController = initAvailabilityEdit({
    onSuccess: loadAvailabilities,
});

async function loadAttendants() {
    if (!availabilityAttendant || !auth.hasPermission('user.list')) {
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

        availabilityCreateController.setAttendantOptions(options);
        availabilityEditController.setAttendantOptions(options);
    } catch (error) {
        toast({
            icon: 'error',
            title: 'Erro ao carregar atendentes',
            text: error.message || 'Não foi possível carregar a lista de atendentes.',
        });
    }
}

if (!canCreateAvailability && createAvailabilityButton) {
    createAvailabilityButton.classList.add('d-none');
}

availabilitiesTableBody?.addEventListener('click', (event) => {
    const button = event.target.closest('[data-action]');

    if (!button) {
        return;
    }

    const { action, availabilityId } = button.dataset;
    const availability = getAvailabilityById(availabilityId);

    if (!availability) {
        toast({
            icon: 'error',
            title: 'Disponibilidade não encontrada',
            text: 'Não foi possível localizar os dados da disponibilidade selecionada.',
        });
        return;
    }

    if (action === 'edit') {
        if (!canUpdateAvailability) {
            toast({
                icon: 'warning',
                title: 'Ação não permitida',
                text: 'Você não tem permissão para editar disponibilidades.',
            });
            return;
        }

        availabilityEditController.openEditAvailabilityModal(availability);
    }
});

await loadAttendants();
await loadAvailabilities();
