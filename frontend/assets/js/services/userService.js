import { http } from '../http.js';

export const userService = {
    list() {
        return http.get('/users');
    },

    create(payload) {
        return http.post('/users', payload);
    },

    update(id, payload) {
        return http.put(`/users/${id}`, payload);
    },

    remove(id) {
        return http.delete(`/users/${id}`);
    },

    changePassword(id, payload) {
        return http.patch(`/users/${id}/password`, payload);
    },

    updateStatus(id, payload) {
        return http.patch(`/users/${id}/status`, payload);
    },
};
