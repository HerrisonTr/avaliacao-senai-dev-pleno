import { http } from '../http.js';

function buildQueryString(params = {}) {
    const query = new URLSearchParams();

    Object.entries(params).forEach(([key, value]) => {
        if (value === '' || value === null || value === undefined) {
            return;
        }

        query.append(key, String(value));
    });

    const queryString = query.toString();
    return queryString ? `?${queryString}` : '';
}

export const scheduleService = {
    list(params = {}) {
        return http.get(`/appointments${buildQueryString(params)}`);
    },

    find(id) {
        return http.get(`/appointments/${id}`);
    },

    create(payload) {
        return http.post('/appointments', payload);
    },

    update(id, payload) {
        return http.put(`/appointments/${id}`, payload);
    },

    updateStatus(id, payload) {
        return http.patch(`/appointments/${id}/status`, payload);
    },

    availableTimes(params = {}) {
        return http.get(`/appointments/available-times${buildQueryString(params)}`);
    },

    availableAttendants(params = {}) {
        return http.get(`/appointments/available-attendants${buildQueryString(params)}`);
    },
};
