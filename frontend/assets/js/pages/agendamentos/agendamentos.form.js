export const appointmentStatusLabels = {
    scheduled: 'Agendado',
    completed: 'Concluído',
    cancelled: 'Cancelado',
};

export function validateAppointmentForm(payload) {
    const errors = {};

    if (!payload.attendant_id) {
        errors.attendant_id = 'Selecione o atendente.';
    }

    if (!payload.service_id) {
        errors.service_id = 'Selecione o serviço.';
    }

    if (!payload.appointment_date) {
        errors.appointment_date = 'Informe a data do agendamento.';
    }

    if (!payload.start_time) {
        errors.start_time = 'Selecione um horário disponível.';
    }

    if (!payload.end_time) {
        errors.end_time = 'O horário de fim não foi definido.';
    } else if (payload.start_time && payload.end_time <= payload.start_time) {
        errors.end_time = 'O horário de fim deve ser maior que o horário de início.';
    }

    if (!payload.customer_name) {
        errors.customer_name = 'Informe o nome do cliente.';
    }

    if (!payload.customer_phone) {
        errors.customer_phone = 'Informe o telefone do cliente.';
    }

    return errors;
}

export function buildTimeOptions(slots, currentSlot = null) {
    const timeValues = new Set();
    const options = [
        '<option value="">Selecione um horário</option>',
    ];

    if (currentSlot?.start_time && currentSlot?.end_time) {
        timeValues.add(currentSlot.start_time);
        timeValues.add(currentSlot.end_time);
    }

    slots.forEach((slot) => {
        timeValues.add(slot.start_time);
        timeValues.add(slot.end_time);
    });

    Array.from(timeValues)
        .sort((firstTime, secondTime) => firstTime.localeCompare(secondTime))
        .forEach((time) => {
            options.push(`
                <option value="${time}">
                    ${time}
                </option>
            `);
        });

    return {
        options: options.join(''),
        slots,
    };
}

export function formatOccupiedTimes(occupied = []) {
    if (!occupied.length) {
        return '';
    }

    const formattedTimes = occupied
        .map((slot) => `${slot.start_time} às ${slot.end_time}`)
        .join(', ');

    return `Horários ocupados para este dia: ${formattedTimes}.`;
}

export function getStatusBadgeClass(status) {
    if (status === 'completed') {
        return 'text-bg-success';
    }

    if (status === 'cancelled') {
        return 'text-bg-danger';
    }

    return 'text-bg-primary';
}
