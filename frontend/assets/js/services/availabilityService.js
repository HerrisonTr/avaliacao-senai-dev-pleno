import { http } from '../http.js';

export const availabilityService = {
    list() {
        return http.get('/attendant-availabilities');
    },

    find(id) {
        return http.get(`/attendant-availabilities/${id}`);
    },

    create(payload) {
        return http.post('/attendant-availabilities', payload);
    },

    update(id, payload) {
        return http.put(`/attendant-availabilities/${id}`, payload);
    },

    updateStatus(id, payload) {
        return http.patch(`/attendant-availabilities/${id}/status`, payload);
    },

    remove(id) {
        return http.delete(`/attendant-availabilities/${id}`);
    },
};
