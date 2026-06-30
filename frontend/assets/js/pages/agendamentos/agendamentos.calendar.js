function isMobileScreen() {
    return window.innerWidth < 768;
}

function getResponsiveCalendarOptions() {
    if (isMobileScreen()) {
        return {
            initialView: 'listWeek',
            headerToolbar: {
                left: 'prev,next',
                center: 'title',
                right: 'today',
            },
        };
    }

    return {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },
    };
}

function getStatusEventColor(status) {
    if (status === 'completed') {
        return '#198754';
    }

    if (status === 'cancelled') {
        return '#dc3545';
    }

    return '#0d6efd';
}

function buildCalendarEvents(appointments) {
    return appointments
        .filter((appointment) => appointment.appointment_date && appointment.start_time && appointment.end_time)
        .map((appointment) => ({
            id: String(appointment.id),
            title: `${appointment.customer_name ?? 'Sem nome'} • ${appointment.service?.name ?? 'Serviço'}`,
            start: `${appointment.appointment_date}T${appointment.start_time}`,
            end: `${appointment.appointment_date}T${appointment.end_time}`,
            backgroundColor: getStatusEventColor(appointment.status),
            borderColor: getStatusEventColor(appointment.status),
            extendedProps: {
                appointmentId: appointment.id,
            },
        }));
}

export function initAppointmentsCalendar({ selector, onEventClick }) {
    const calendarElement = document.querySelector(selector);

    if (!calendarElement || typeof FullCalendar === 'undefined') {
        return null;
    }

    const responsiveOptions = getResponsiveCalendarOptions();

    const calendar = new FullCalendar.Calendar(calendarElement, {
        locale: 'pt-br',
        height: 'auto',
        ...responsiveOptions,
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            day: 'Dia',
            list: 'Lista',
        },
        noEventsContent: 'Nenhum agendamento cadastrado.',
        events: [],
        eventClick(info) {
            onEventClick?.(info.event.extendedProps.appointmentId);
        },
    });

    calendar.render();

    window.addEventListener('resize', () => {
        const options = getResponsiveCalendarOptions();
        calendar.setOption('headerToolbar', options.headerToolbar);
    });

    return {
        setEvents(appointments) {
            calendar.removeAllEvents();
            calendar.addEventSource(buildCalendarEvents(appointments));
        },
    };
}
