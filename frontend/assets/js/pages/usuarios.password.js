import { toast } from '../../ui.js';
import {
    applyValidationErrors,
    clearFieldErrors,
    getTrimmedFormData,
    setButtonLoadingState,
    setFieldError,
} from '../../ui.form.js';
import { userService } from '../../services/userService.js';

const userFormPassword = document.querySelector('#user-form-password');
const userModalPasswordElement = document.querySelector('#modal-senha-usuario');
const userModalPassword = bootstrap.Modal.getOrCreateInstance(userModalPasswordElement);
const userFormPasswordSubmit = document.querySelector('#user-form-password-submit');
const userIdPassword = document.querySelector('#senha-usuario-id');
const userNamePassword = document.querySelector('#senha-usuario-nome');

const userFieldMapPassword = {
    password: document.querySelector('#nova-senha'),
    password_confirmation: document.querySelector('#confirmar-nova-senha'),
};

function validatePasswordUserForm(payload) {
    const errors = {};

    if (!payload.password) {
        errors.password = 'Informe a senha.';
    } else if (payload.password.length < 8) {
        errors.password = 'A senha deve ter no mínimo 8 caracteres.';
    } else {
        const hasLowercase = /[a-z]/.test(payload.password);
        const hasUppercase = /[A-Z]/.test(payload.password);

        if (!hasLowercase || !hasUppercase) {
            errors.password = 'A senha deve conter letras maiúsculas e minúsculas.';
        }
    }

    if (!payload.password_confirmation) {
        errors.password_confirmation = 'Confirme a senha.';
    } else if (payload.password !== payload.password_confirmation) {
        errors.password_confirmation = 'A confirmação da senha não confere.';
    }

    return errors;
}

function resetPasswordFormState() {
    userFormPassword.reset();
    userIdPassword.value = '';
    userNamePassword.value = '';
    clearFieldErrors(userFieldMapPassword);
}

function openPasswordModal(user) {
    userIdPassword.value = user.id;
    userNamePassword.value = user.name ?? '';
    clearFieldErrors(userFieldMapPassword);
    userModalPassword.show();
}

export function initUserPassword({ onSuccess }) {
    userModalPasswordElement.addEventListener('hidden.bs.modal', () => {
        resetPasswordFormState();
    });

    userFormPassword.addEventListener('submit', async (event) => {
        event.preventDefault();

        clearFieldErrors(userFieldMapPassword);

        const userId = userIdPassword.value;
        const payload = getTrimmedFormData(userFormPassword);
        const validationErrors = validatePasswordUserForm(payload);

        if (Object.keys(validationErrors).length > 0) {
            Object.entries(validationErrors).forEach(([field, message]) => {
                setFieldError(userFieldMapPassword, field, message);
            });
            toast({
                icon: 'warning',
                title: 'Validação',
                text: 'Corrija os campos destacados e tente novamente.',
            });
            return;
        }

        setButtonLoadingState(userFormPasswordSubmit, 'Salvando...', true);

        try {
            const response = await userService.changePassword(userId, payload);

            userModalPassword.hide();
            resetPasswordFormState();
            await onSuccess();
            toast({
                icon: 'success',
                title: 'Sucesso',
                text: response.message || 'Senha atualizada com sucesso.',
            });
        } catch (error) {
            if (error.status === 422) {
                applyValidationErrors(userFieldMapPassword, error.data?.errors);
                toast({
                    icon: 'warning',
                    title: 'Verifique os dados',
                    text: error.message || 'Verifique os dados informados.',
                });
            } else {
                toast({
                    icon: 'error',
                    title: 'Erro ao alterar senha',
                    text: error.message || 'Não foi possível atualizar a senha do usuário.',
                });
            }
        } finally {
            setButtonLoadingState(userFormPasswordSubmit, 'Salvando...', false);
        }
    });

    return {
        openPasswordModal,
    };
}
