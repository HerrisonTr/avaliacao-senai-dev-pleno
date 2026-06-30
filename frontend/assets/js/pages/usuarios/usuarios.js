import { auth, getUser } from '../../auth.js';
import { escapeHtml, toast } from '../../ui.js';
import { userService } from '../../services/userService.js';
import { initUserCreate } from './usuarios.create.js';
import { confirmAndDeleteUser } from './usuarios.delete.js';
import { initUserEdit } from './usuarios.edit.js';
import { initUserPassword } from './usuarios.password.js';

await auth.protectPage();

const usersTableBody = document.querySelector('#users-table-body');
const createUserButton = document.querySelector('[data-bs-target="#modal-cadastro-usuario"]');
const createUserModalElement = document.querySelector('#modal-cadastro-usuario');
const authenticatedUser = getUser();
const authenticatedUserId = Number(authenticatedUser?.id ?? 0);

const canCreateUser = auth.hasPermission('user.create');
const canUpdateUser = auth.hasPermission('user.update');
const canDeleteUser = auth.hasPermission('user.delete');

let usersCache = [];

function canEditUser(userId) {
    return canUpdateUser || Number(userId) === authenticatedUserId;
}

function canChangePassword(userId) {
    return canUpdateUser || Number(userId) === authenticatedUserId;
}

function canDeleteTargetUser(userId) {
    return canDeleteUser && Number(userId) !== authenticatedUserId;
}

function buildActionButtons(userId) {
    const actions = [];

    if (canEditUser(userId)) {
        actions.push(`
            <button type="button" class="btn btn-warning btn-sm" title="Editar" data-action="edit" data-user-id="${userId}">
                <i class="bi bi-pencil-square"></i>
            </button>
        `);
    }

    if (canChangePassword(userId)) {
        actions.push(`
            <button type="button" class="btn btn-secondary btn-sm" title="Alterar senha" data-action="password" data-user-id="${userId}">
                <i class="bi bi-lock-fill"></i>
            </button>
        `);
    }

    if (canDeleteTargetUser(userId)) {
        actions.push(`
            <button type="button" class="btn btn-danger btn-sm" title="Excluir" data-action="delete" data-user-id="${userId}">
                <i class="bi bi-trash-fill"></i>
            </button>
        `);
    }

    if (!actions.length) {
        return '<span class="text-muted">Sem ações disponíveis</span>';
    }

    return `
        <div class="btn-group" role="group" aria-label="Ações do usuário">
            ${actions.join('')}
        </div>
    `;
}

function renderUsers(users) {
    if (!users.length) {
        usersTableBody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-muted py-4">Nenhum usuário cadastrado.</td>
            </tr>
        `;
        return;
    }

    usersTableBody.innerHTML = users.map((user) => `
        <tr>
            <td>${escapeHtml(user.name)}</td>
            <td>${escapeHtml(user.email)}</td>
            <td>${escapeHtml(user.role ?? '-')}</td>
            <td class="text-center">${buildActionButtons(user.id)}</td>
        </tr>
    `).join('');
}

async function loadUsers() {
    usersTableBody.innerHTML = `
        <tr>
            <td colspan="4" class="text-center text-muted py-4">Carregando usuários...</td>
        </tr>
    `;

    try {
        const response = await userService.list();
        usersCache = response.data ?? [];
        renderUsers(usersCache);
    } catch (error) {
        usersTableBody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-danger py-4">Não foi possível carregar os usuários.</td>
            </tr>
        `;
        toast({
            icon: 'error',
            title: 'Erro ao listar usuários',
            text: error.message || 'Não foi possível carregar os usuários.',
        });
    }
}

function getUserById(userId) {
    return usersCache.find((user) => Number(user.id) === Number(userId)) ?? null;
}

const userEditController = initUserEdit({
    onSuccess: loadUsers,
});

const userPasswordController = initUserPassword({
    onSuccess: loadUsers,
});

if (canCreateUser) {
    initUserCreate({
        onSuccess: loadUsers,
    });
} else if (createUserButton) {
    createUserButton.classList.add('d-none');
}

createUserModalElement?.addEventListener('show.bs.modal', (event) => {
    if (canCreateUser) {
        return;
    }

    event.preventDefault();
    toast({
        icon: 'warning',
        title: 'Ação não permitida',
        text: 'Você não tem permissão para cadastrar usuários.',
    });
});

usersTableBody.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-action]');

    if (!button) {
        return;
    }

    const { action, userId } = button.dataset;
    const user = getUserById(userId);

    if (!user) {
        toast({
            icon: 'error',
            title: 'Usuário não encontrado',
            text: 'Não foi possível localizar os dados do usuário selecionado.',
        });
        return;
    }

    if (action === 'edit' && !canEditUser(user.id)) {
        toast({
            icon: 'warning',
            title: 'Ação não permitida',
            text: 'Você não tem permissão para editar este usuário.',
        });
        return;
    }

    if (action === 'edit') {
        userEditController.openEditModal(user);
        return;
    }

    if (action === 'password' && !canChangePassword(user.id)) {
        toast({
            icon: 'warning',
            title: 'Ação não permitida',
            text: 'Você não tem permissão para alterar a senha deste usuário.',
        });
        return;
    }

    if (action === 'password') {
        userPasswordController.openPasswordModal(user);
        return;
    }

    if (action === 'delete') {
        if (!canDeleteTargetUser(user.id)) {
            toast({
                icon: 'warning',
                title: 'Ação não permitida',
                text: Number(user.id) === authenticatedUserId
                    ? 'Você não pode excluir o usuário autenticado.'
                    : 'Você não tem permissão para excluir este usuário.',
            });
            return;
        }

        await confirmAndDeleteUser(user, {
            onSuccess: loadUsers,
        });
    }
});

await loadUsers();
