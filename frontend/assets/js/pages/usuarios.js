import { auth } from '../auth.js';
import { escapeHtml, toast } from '../ui.js';
import { userService } from '../services/userService.js';
import { initUserCreate } from './usuarios.create.js';
import { confirmAndDeleteUser } from './usuarios.delete.js';
import { initUserEdit } from './usuarios.edit.js';
import { initUserPassword } from './usuarios.password.js';

await auth.protectPage();

const usersTableBody = document.querySelector('#users-table-body');

let usersCache = [];

function buildActionButtons(userId) {
    return `
        <div class="btn-group" role="group" aria-label="Ações do usuário">
            <button type="button" class="btn btn-warning btn-sm" title="Editar" data-action="edit" data-user-id="${userId}">
                <i class="bi bi-pencil-square"></i>
            </button>
            <button type="button" class="btn btn-secondary btn-sm" title="Alterar senha" data-action="password" data-user-id="${userId}">
                <i class="bi bi-lock-fill"></i>
            </button>
            <button type="button" class="btn btn-danger btn-sm" title="Excluir" data-action="delete" data-user-id="${userId}">
                <i class="bi bi-trash-fill"></i>
            </button>
        </div>
    `;
}

function renderUsers(users) {
    if (!users.length) {
        usersTableBody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center text-muted py-4">Nenhum usuário cadastrado.</td>
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
            <td colspan="5" class="text-center text-muted py-4">Carregando usuários...</td>
        </tr>
    `;

    try {
        const response = await userService.list();
        usersCache = response.data ?? [];
        renderUsers(usersCache);
    } catch (error) {
        usersTableBody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center text-danger py-4">Não foi possível carregar os usuários.</td>
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

initUserCreate({
    onSuccess: loadUsers,
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

    if (action === 'edit') {
        userEditController.openEditModal(user);
        return;
    }

    if (action === 'password') {
        userPasswordController.openPasswordModal(user);
        return;
    }

    if (action === 'delete') {
        await confirmAndDeleteUser(user, {
            onSuccess: loadUsers,
        });
    }
});

await loadUsers();
