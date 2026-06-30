import { toast } from '../ui.js';
import {
    applyValidationErrors,
    clearFieldErrors,
    getTrimmedFormData,
    setButtonLoadingState,
    setFieldError,
} from '../ui.form.js';
import { userService } from '../services/userService.js';

const userFormStore = document.querySelector('#user-form');
const userModalStoreElement = document.querySelector('#modal-cadastro-usuario');
const userModalStore = bootstrap.Modal.getOrCreateInstance(userModalStoreElement);
const userFormStoreSubmit = document.querySelector('#user-form-submit');

const userFieldMapStore = {
    name: document.querySelector('#nome'),
    email: document.querySelector('#email'),
    role_id: document.querySelector('#perfil'),
    password: document.querySelector('#senha'),
    password_confirmation: document.querySelector('#confirmar-senha'),
};

function validateStoreUserForm(payload) {
    const errors = {};

    if (!payload.name) {
        errors.name = 'Informe o nome.';
    }

    if (!payload.email) {
        errors.email = 'Informe o e-mail.';
    }

    if (!payload.role_id) {
        errors.role_id = 'Selecione o perfil.';
    }

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

function resetStoreFormState() {
    userFormStore.reset();
    clearFieldErrors(userFieldMapStore);
}

export function initUserCreate({ onSuccess }) {
    userModalStoreElement.addEventListener('hidden.bs.modal', () => {
        resetStoreFormState();
    });

    userFormStore.addEventListener('submit', async (event) => {
        event.preventDefault();

        clearFieldErrors(userFieldMapStore);

        const payload = getTrimmedFormData(userFormStore, ['role_id']);
        const validationErrors = validateStoreUserForm(payload);

        if (Object.keys(validationErrors).length > 0) {
            Object.entries(validationErrors).forEach(([field, message]) => {
                setFieldError(userFieldMapStore, field, message);
            });
            toast({
                icon: 'warning',
                title: 'Validação',
                text: 'Corrija os campos destacados e tente novamente.',
            });
            return;
        }

        setButtonLoadingState(userFormStoreSubmit, 'Salvando...', true);

        try {
            const response = await userService.create(payload);

            userModalStore.hide();
            resetStoreFormState();
            await onSuccess();
            toast({
                icon: 'success',
                title: 'Sucesso',
                text: response.message || 'Usuário cadastrado com sucesso.',
            });
        } catch (error) {
            if (error.status === 422) {
                applyValidationErrors(userFieldMapStore, error.data?.errors);
                toast({
                    icon: 'warning',
                    title: 'Verifique os dados',
                    text: error.message || 'Verifique os dados informados.',
                });
            } else {
                toast({
                    icon: 'error',
                    title: 'Erro ao cadastrar usuário',
                    text: error.message || 'Não foi possível cadastrar o usuário.',
                });
            }
        } finally {
            setButtonLoadingState(userFormStoreSubmit, 'Salvando...', false);
        }
    });
}
