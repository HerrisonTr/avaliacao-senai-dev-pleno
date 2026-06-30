import { toast } from '../../ui.js';
import {
    applyValidationErrors,
    clearFieldErrors,
    getTrimmedFormData,
    setButtonLoadingState,
    setFieldError,
} from '../../ui.form.js';

import { userService } from '../../services/userService.js';

const userFormEdit = document.querySelector('#user-form-edit');
const userModalEditElement = document.querySelector('#modal-edicao-usuario');
const userModalEdit = bootstrap.Modal.getOrCreateInstance(userModalEditElement);
const userFormEditSubmit = document.querySelector('#user-form-edit-submit');
const userIdEdit = document.querySelector('#editar-usuario-id');

const userFieldMapEdit = {
    name: document.querySelector('#editar-nome'),
    email: document.querySelector('#editar-email'),
    role_id: document.querySelector('#editar-perfil'),
};

function validateEditUserForm(payload) {
    const errors = {};

    if (!payload.name) {
        errors.name = 'Informe o nome.';
    }

    if (!payload.role_id) {
        errors.role_id = 'Selecione o perfil.';
    }

    return errors;
}

function mapRoleNameToId(roleName) {
    return roleName === 'Administrador' ? '1' : '2';
}

function resetEditFormState() {
    userFormEdit.reset();
    userIdEdit.value = '';
    clearFieldErrors(userFieldMapEdit);
}

function openEditModal(user) {
    userIdEdit.value = user.id;
    userFieldMapEdit.name.value = user.name ?? '';
    userFieldMapEdit.email.value = user.email ?? '';
    userFieldMapEdit.role_id.value = mapRoleNameToId(user.role);
    clearFieldErrors(userFieldMapEdit);
    userModalEdit.show();
}

export function initUserEdit({ onSuccess }) {
    userModalEditElement.addEventListener('hidden.bs.modal', () => {
        resetEditFormState();
    });

    userFormEdit.addEventListener('submit', async (event) => {
        event.preventDefault();

        clearFieldErrors(userFieldMapEdit);

        const userId = userIdEdit.value;
        const payload = getTrimmedFormData(userFormEdit, ['role_id']);
        const validationErrors = validateEditUserForm(payload);

        if (Object.keys(validationErrors).length > 0) {
            Object.entries(validationErrors).forEach(([field, message]) => {
                setFieldError(userFieldMapEdit, field, message);
            });
            toast({
                icon: 'warning',
                title: 'Validação',
                text: 'Corrija os campos destacados e tente novamente.',
            });
            return;
        }

        setButtonLoadingState(userFormEditSubmit, 'Salvando...', true);

        try {
            const response = await userService.update(userId, payload);

            userModalEdit.hide();
            resetEditFormState();
            await onSuccess();
            toast({
                icon: 'success',
                title: 'Sucesso',
                text: response.message || 'Usuário atualizado com sucesso.',
            });
        } catch (error) {
            if (error.status === 422) {
                applyValidationErrors(userFieldMapEdit, error.data?.errors);
                toast({
                    icon: 'warning',
                    title: 'Verifique os dados',
                    text: error.message || 'Verifique os dados informados.',
                });
            } else {
                toast({
                    icon: 'error',
                    title: 'Erro ao editar usuário',
                    text: error.message || 'Não foi possível atualizar o usuário.',
                });
            }
        } finally {
            setButtonLoadingState(userFormEditSubmit, 'Salvando...', false);
        }
    });

    return {
        openEditModal,
    };
}
