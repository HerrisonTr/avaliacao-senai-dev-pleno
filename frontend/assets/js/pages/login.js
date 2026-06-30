import { auth, consumeLoginMessage } from '../auth.js';
import { base_url } from '../config.js';

const form = document.querySelector('#login-form');
const feedback = document.querySelector('#login-feedback');
const submitButton = document.querySelector('#login-submit');

const showError = (message) => {
    feedback.textContent = message;
    feedback.classList.remove('d-none');
};

const hideError = () => {
    feedback.textContent = '';
    feedback.classList.add('d-none');
};

if (auth.isAuthenticated()) {
    window.location.href = base_url('pages/dashboard');
}

const redirectedMessage = consumeLoginMessage();
if (redirectedMessage) {
    showError(redirectedMessage);
}

form.addEventListener('submit', async (event) => {
    event.preventDefault();
    hideError();

    const formData = new FormData(form);
    const email = String(formData.get('email') || '').trim();
    const senha = String(formData.get('senha') || '');

    if (!email || !senha) {
        showError('Informe e-mail e senha para continuar.');
        return;
    }

    submitButton.disabled = true;
    submitButton.textContent = 'Entrando...';

    try {
        await auth.login(email, senha);
        window.location.href = base_url('pages/dashboard');
    } catch (error) {
        showError(error.message || 'Não foi possível realizar o login.');
    } finally {
        submitButton.disabled = false;
        submitButton.textContent = 'Entrar';
    }
});
