export const weekdayLabels = {
    0: 'Domingo',
    1: 'Segunda-feira',
    2: 'Terça-feira',
    3: 'Quarta-feira',
    4: 'Quinta-feira',
    5: 'Sexta-feira',
    6: 'Sábado',
};

export function validateAvailabilityForm(payload) {
    const errors = {};

    if (!payload.attendant_id) {
        errors.attendant_id = 'Selecione o atendente.';
    }

    if (payload.day_of_week === '' || payload.day_of_week === null || Number.isNaN(payload.day_of_week)) {
        errors.day_of_week = 'Selecione o dia da semana.';
    }

    if (!payload.start_time) {
        errors.start_time = 'Informe a hora inicial.';
    }

    if (!payload.end_time) {
        errors.end_time = 'Informe a hora final.';
    } else if (payload.start_time && payload.end_time <= payload.start_time) {
        errors.end_time = 'A hora final deve ser maior que a hora inicial.';
    }

    return errors;
}
