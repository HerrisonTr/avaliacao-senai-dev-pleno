import { http } from './http.js';
import { base_url } from './config.js';

const TOKEN_KEY = 'access_token';
const USER_KEY = 'auth_user';
const LOGIN_MESSAGE_KEY = 'login_message';

export function setToken(token) {
    localStorage.setItem(TOKEN_KEY, token);
}

export function getToken() {
    return localStorage.getItem(TOKEN_KEY);
}

export function removeToken() {
    localStorage.removeItem(TOKEN_KEY);
}

export function setUser(user) {
    localStorage.setItem(USER_KEY, JSON.stringify(user));
}

export function getUser() {
    const user = localStorage.getItem(USER_KEY);

    if (!user) {
        return null;
    }

    try {
        return JSON.parse(user);
    } catch {
        return null;
    }
}

export function removeUser() {
    localStorage.removeItem(USER_KEY);
}

export function getPermissions() {
    return getUser()?.permissions ?? [];
}

export function hasPermission(permission) {
    return getPermissions().includes(permission);
}

export function clearSession() {
    removeToken();
    removeUser();
}

export function setLoginMessage(message) {
    if (message) {
        sessionStorage.setItem(LOGIN_MESSAGE_KEY, message);
    }
}

export function consumeLoginMessage() {
    const message = sessionStorage.getItem(LOGIN_MESSAGE_KEY);

    if (message) {
        sessionStorage.removeItem(LOGIN_MESSAGE_KEY);
    }

    return message;
}

export function redirectToLogin(message = '') {
    setLoginMessage(message);
    window.location.href = base_url('index');
}

export function redirectToDashboard() {
    window.location.href = base_url('pages/dashboard');
}

export function handleUnauthorized(message = 'Sua sessão expirou. Faça login novamente.') {
    clearSession();
    redirectToLogin(message);
}

export const auth = {
    setToken,
    getToken,
    removeToken,
    setUser,
    getUser,
    getPermissions,
    hasPermission,
    removeUser,
    clearSession,

    isAuthenticated() {
        return Boolean(getToken());
    },

    isAdmin() {
        const user = getUser();
        return user?.role === 'Administrador';
    },

    isAttendant() {
        const user = getUser();
        return user?.role === 'Atendente';
    },

    async login(email, password) {
        const response = await http.post('/login', {
            email,
            password,
        });

        setToken(response.token);

        if (response.user) {
            setUser(response.user);
        } else {
            await this.me();
        }

        return response;
    },

    async logout() {
        try {
            await http.post('/logout');
        } finally {
            clearSession();
            redirectToLogin('Logout realizado com sucesso.');
        }
    },

    async me() {
        const response = await http.get('/me');
        const user = response?.user ?? response;

        if (user) {
            setUser(user);
        }

        return user;
    },

    async protectPage(options = {}) {
        const {
            requireAdmin = false,
            redirectIfUnauthorized = true,
        } = options;

        if (!this.isAuthenticated()) {
            if (redirectIfUnauthorized) {
                redirectToLogin('Faça login para acessar esta página.');
            }

            return false;
        }

        try {
            const user = await this.me();

            if (requireAdmin && user?.role !== 'Administrador') {
                if (redirectIfUnauthorized) {
                    redirectToDashboard();
                }

                return false;
            }

            return true;
        } catch (error) {
            clearSession();

            if (redirectIfUnauthorized) {
                redirectToLogin('Sua sessão expirou. Faça login novamente.');
            }

            return false;
        }
    },

    requireAuth() {
        if (!this.isAuthenticated()) {
            redirectToLogin('Faça login para continuar.');
            return false;
        }

        return true;
    },

    requireAdmin() {
        if (!this.requireAuth()) {
            return false;
        }

        if (!this.isAdmin()) {
            redirectToDashboard();
            return false;
        }

        return true;
    },
};
