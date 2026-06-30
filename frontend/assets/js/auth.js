import { http } from './http.js';
import { base_url } from './config.js';

const TOKEN_KEY = 'access_token';
const USER_KEY = 'auth_user';

export const auth = {
    setToken(token) {
        localStorage.setItem(TOKEN_KEY, token);
    },

    getToken() {
        return localStorage.getItem(TOKEN_KEY);
    },

    removeToken() {
        localStorage.removeItem(TOKEN_KEY);
    },

    setUser(user) {
        localStorage.setItem(USER_KEY, JSON.stringify(user));
    },

    getUser() {
        const user = localStorage.getItem(USER_KEY);

        if (!user) {
            return null;
        }

        try {
            return JSON.parse(user);
        } catch {
            return null;
        }
    },

    removeUser() {
        localStorage.removeItem(USER_KEY);
    },

    isAuthenticated() {
        return Boolean(this.getToken());
    },

    isAdmin() {
        const user = this.getUser();
        return user?.role === 'Administrador' || user?.role === 'admin';
    },

    isAttendant() {
        const user = this.getUser();
        return user?.role === 'Atendente' || user?.role === 'attendant';
    },

    async login(email, password) {
        const response = await http.post('/login', {
            email,
            password,
        });

        this.setToken(response.token);

        if (response.user) {
            this.setUser(response.user);
        } else {
            const user = await this.me();
            this.setUser(user);
        }

        return response;
    },

    async logout() {
        try {
            await http.post('/logout');
        } finally {
            this.clearSession();
            window.location.href = base_url('index');
        }
    },

    async me() {
        const user = await http.get('/me');
        this.setUser(user);
        return user;
    },

    clearSession() {
        this.removeToken();
        this.removeUser();
    },

    requireAuth() {
        if (!this.isAuthenticated()) {
            window.location.href = base_url('index');
            return false;
        }

        return true;
    },

    requireAdmin() {
        if (!this.requireAuth()) {
            return false;
        }

        if (!this.isAdmin()) {
            window.location.href = base_url('index');
            return false;
        }

        return true;
    },
};