function notifyMessage(text, type) {
    const toast = Swal.mixin({
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (element) => {
            element.addEventListener('mouseenter', Swal.stopTimer);
            element.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    toast.fire({
        icon: type,
        title: text
    });
}

function modalClose() {
    $('#login').modal('hide');
}

function readJsonPayload(elementId) {
    const element = document.getElementById(elementId);
    if (!element) {
        return [];
    }

    try {
        return JSON.parse(element.textContent || '[]');
    } catch (error) {
        console.error('Failed to parse calendar payload', error);
        return [];
    }
}

function bindCommonUi() {
    const yearNode = document.getElementById('year');
    if (yearNode) {
        yearNode.textContent = `2017 - ${new Date().getFullYear().toString()}`;
    }

    if (window.Fancybox) {
        Fancybox.bind('[data-fancybox="gallery"]', {
            hideScrollbar: false
        });
    }
}

function initPublicReservationCalendar(calendarElement) {
    const events = readJsonPayload('reservation-calendar-events');

    $(calendarElement).fullCalendar({
        locale: 'cs',
        minTime: '08:00:00',
        maxTime: '18:00:00',
        height: 510,
        columnFormat: 'D.M',
        slotMinutes: 30,
        defaultView: 'agendaWeek',
        slotLabelFormat: 'H:mm',
        titleFormat: 'D.MMMM YYYY',
        weekends: false,
        editable: false,
        allDaySlot: false,
        header: {
            left: '',
            center: 'title',
            right: 'prev,next today'
        },
        events,
        eventClick: (calendarEvent) => {
            if (calendarEvent.color === 'red') {
                notifyMessage('Oaza uz je plně obsazena. Vyberte prosím jinou hodinu', 'error');
                return;
            }

            $('#createRezervationModal').modal('toggle');
            $('#dateTimeCreation').text(moment(calendarEvent.start).format('DD.M.YYYY H:00'));
            $('input[name=date]').val(moment(calendarEvent.start).format('YYYY-MM-DD HH:00:00'));
        },
        buttonText: {
            today: 'Dnes'
        },
        weekMode: 'variable'
    });
}

function initUserCalendar(calendarElement) {
    const events = readJsonPayload('user-calendar-events');
    const cancelUrl = calendarElement.dataset.cancelUrl;
    const cancelButton = $('#cancelReservationButton');

    const cancelReservation = (reservationId) => {
        if (!cancelUrl || reservationId <= 0) {
            return;
        }

        $.nette.ajax({
            url: cancelUrl,
            type: 'GET',
            data: {
                reservationId
            }
        });
    };

    cancelButton.off('click').on('click', () => {
        const reservationId = Number(cancelButton.data('reservationId') || 0);
        cancelReservation(reservationId);
    });

    $(calendarElement).fullCalendar({
        locale: 'cs',
        defaultView: 'month',
        contentHeight: 500,
        timeFormat: 'H:mm',
        header: {
            left: '',
            center: 'title',
            right: 'prev,next today'
        },
        editable: false,
        events,
        eventClick: (calendarEvent) => {
            const reservationDate = new Date(calendarEvent.start);
            const canCancel = reservationDate >= new Date();

            $('#showReservationUserCalendar').modal('toggle');
            $('#totalCount').text(calendarEvent.totalCount ?? '');
            $('#date').text(calendarEvent.date ?? '');
            $('#hasChildren').text(calendarEvent.hasChildren ?? '');
            $('#comment').text(calendarEvent.comment ?? '');

            cancelButton.data('reservationId', canCancel ? calendarEvent.id : 0);
            cancelButton.prop('disabled', !canCancel);
        },
        buttonText: {
            today: 'Dnes'
        },
        weekMode: 'variable'
    });
}

$(function () {
    bindCommonUi();

    const calendarElement = document.getElementById('calendar');
    if (!calendarElement || typeof $.fn.fullCalendar !== 'function') {
        return;
    }

    if (calendarElement.dataset.calendarKind === 'public') {
        initPublicReservationCalendar(calendarElement);
    }

    if (calendarElement.dataset.calendarKind === 'user') {
        initUserCalendar(calendarElement);
    }
});
