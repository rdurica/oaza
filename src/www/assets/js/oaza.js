function notifyMessage(text, type) {
    const toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        customClass: { popup: 'oaza-toast' },
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

function normalizeCalendarColor(color) {
    if (typeof color !== 'string') {
        return '';
    }

    return color.trim().toLowerCase();
}

function isMobileViewport() {
    return window.innerWidth < 768;
}

function resolveCalendarEventClass(event) {
    const color = normalizeCalendarColor(event.backgroundColor || event.extendedProps.color || '');
    if (color === 'green') return 'oaza-calendar-event--free';
    if (color === '#0092ad') return 'oaza-calendar-event--partial';
    if (color === 'red') return 'oaza-calendar-event--full';
    if (color === 'grey' || color === 'gray') return 'oaza-calendar-event--muted';
    return 'oaza-calendar-event--default';
}

function isFullyBookedEvent(event) {
    return normalizeCalendarColor(event.backgroundColor) === 'red';
}

function styleCalendarEvent(event, el) {
    if (!el) return;
    el.classList.add('oaza-calendar-event', resolveCalendarEventClass(event));
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

function initPublicMobileNav() {
    const header = document.querySelector('.public-top-bar');
    if (!header || typeof $.fn.collapse !== 'function') {
        return;
    }

    const toggle = header.querySelector('.public-nav-toggle');
    const nav = document.getElementById('public-mobile-nav');
    if (!toggle || !nav) {
        return;
    }

    const mobileQuery = window.matchMedia('(max-width: 767px)');
    const toggleNode = $(toggle);
    const navNode = $(nav);
    const bodyNode = $(document.body);
    const headerNode = $(header);

    const syncHeaderHeight = () => {
        const headerHeight = Math.round(header.getBoundingClientRect().height);
        header.style.setProperty('--public-mobile-header-height', `${headerHeight}px`);
    };

    const syncState = (isOpen) => {
        toggleNode.attr('aria-expanded', isOpen ? 'true' : 'false');
        toggle.classList.toggle('is-open', isOpen);
        bodyNode.toggleClass('nav-open', isOpen);
        headerNode.toggleClass('nav-open', isOpen);
    };

    const instantClose = () => {
        navNode.addClass('is-closing');
        navNode.removeClass('in collapsing').addClass('collapse').css('height', '');
        navNode.find('.dropdown.open').removeClass('open');
        syncState(false);

        window.setTimeout(() => {
            navNode.removeClass('is-closing');
        }, 260);
    };

    const closeMenu = (options = {}) => {
        if (options.instant && mobileQuery.matches) {
            instantClose();
            return;
        }

        if (navNode.hasClass('in') || navNode.hasClass('collapsing')) {
            navNode.collapse('hide');
            return;
        }

        syncState(false);
    };

    navNode.on('show.bs.collapse', () => {
        syncHeaderHeight();
        syncState(true);
    });

    navNode.on('shown.bs.collapse', () => {
        syncState(true);
    });

    navNode.on('hide.bs.collapse', () => {
        syncState(false);
    });

    navNode.on('hidden.bs.collapse', () => {
        syncState(false);
        navNode.find('.dropdown.open').removeClass('open');
    });

    toggleNode.on('click.publicNav', (event) => {
        if (!mobileQuery.matches) {
            return;
        }

        if (navNode.hasClass('in') || navNode.hasClass('collapsing')) {
            event.preventDefault();
            event.stopPropagation();
            closeMenu({ instant: true });
        }
    });

    navNode.on('click', '.dropdown > .dropdown-toggle', function (event) {
        if (!mobileQuery.matches) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const currentDropdown = $(this).parent('.dropdown');
        const shouldOpen = !currentDropdown.hasClass('open');

        navNode.find('.dropdown.open').not(currentDropdown).removeClass('open');
        currentDropdown.toggleClass('open', shouldOpen);
    });

    navNode.on('click', 'a', function () {
        if (!mobileQuery.matches || $(this).hasClass('dropdown-toggle')) {
            return;
        }

        closeMenu({ instant: true });
    });

    $(document).on('keydown.publicNav', (event) => {
        if (event.key === 'Escape' && navNode.hasClass('in')) {
            closeMenu({ instant: true });
        }
    });

    const handleViewportChange = () => {
        if (mobileQuery.matches) {
            syncHeaderHeight();
            return;
        }

        navNode.removeClass('is-closing');
        navNode.find('.dropdown.open').removeClass('open');
        closeMenu({ instant: true });
        navNode.removeAttr('style');
    };

    if (typeof mobileQuery.addEventListener === 'function') {
        mobileQuery.addEventListener('change', handleViewportChange);
    } else if (typeof mobileQuery.addListener === 'function') {
        mobileQuery.addListener(handleViewportChange);
    }

    $(window).on('resize.publicNav', syncHeaderHeight);

    syncHeaderHeight();
    syncState(navNode.hasClass('in'));
}

function initPublicReservationCalendar(calendarElement) {
    const events = readJsonPayload('reservation-calendar-events');
    const isMobile = isMobileViewport();

    const calendar = new FullCalendar.Calendar(calendarElement, {
        locale: 'cs',
        initialView: isMobile ? 'listWeek' : 'timeGridWeek',
        slotMinTime: '08:00:00',
        slotMaxTime: '18:00:00',
        height: isMobile ? 'auto' : 510,
        dayHeaderFormat: { day: 'numeric', month: 'numeric' },
        slotDuration: '00:30:00',
        slotLabelFormat: { hour: 'numeric', minute: '2-digit', hour12: false },
        weekends: false,
        editable: false,
        allDaySlot: false,
        headerToolbar: { left: '', center: 'title', right: 'prev,next today' },
        buttonText: { today: 'Dnes' },
        events,
        eventDidMount: ({ event, el }) => {
            styleCalendarEvent(event, el);
            if (event.extendedProps.hasChildren === 'Ano') {
                const icon = document.createElement('span');
                icon.className = 'oaza-calendar-children-icon';
                icon.innerHTML = ' <i class="fa-solid fa-child-reaching" title="Přítomnost dětí"></i>';
                const titleEl = el.querySelector('.fc-event-title');
                if (titleEl) titleEl.appendChild(icon);
            }
        },
        eventClick: ({ event }) => {
            if (isFullyBookedEvent(event)) {
                notifyMessage('Oaza uz je plně obsazena. Vyberte prosím jinou hodinu', 'error');
                return;
            }
            const available = event.extendedProps.availableCapacity ?? 5;
            $('#createRezervationModal .radio-pill').each(function () {
                const val = parseInt($(this).find('input[type=radio]').val(), 10);
                $(this).toggle(val <= available);
                if (val > available) {
                    $(this).find('input[type=radio]').prop('checked', false);
                }
            });
            $('#createRezervationModal').modal('toggle');
            $('#dateTimeCreation').text(moment(event.start).format('DD.M.YYYY H:00'));
            $('input[name=date]').val(moment(event.start).format('YYYY-MM-DD HH:00:00'));
        },
        windowResize: ({ view }) => {
            const target = isMobileViewport() ? 'listWeek' : 'timeGridWeek';
            if (view.type !== target) {
                calendar.changeView(target);
                calendar.setOption('height', isMobileViewport() ? 'auto' : 510);
            }
        }
    });
    calendar.render();
}

function initUserCalendar(calendarElement) {
    const events = readJsonPayload('user-calendar-events');
    const cancelUrl = calendarElement.dataset.cancelUrl;
    const cancelButton = $('#cancelReservationButton');

    const cancelReservation = (reservationId) => {
        if (!cancelUrl || reservationId <= 0) return;
        $.nette.ajax({ url: cancelUrl, type: 'GET', data: { reservationId } });
    };

    cancelButton.off('click').on('click', () => {
        cancelReservation(Number(cancelButton.data('reservationId') || 0));
    });

    const isMobile = isMobileViewport();

    const calendar = new FullCalendar.Calendar(calendarElement, {
        locale: 'cs',
        initialView: isMobile ? 'listMonth' : 'dayGridMonth',
        height: isMobile ? 'auto' : 500,
        eventTimeFormat: { hour: 'numeric', minute: '2-digit', hour12: false },
        headerToolbar: { left: '', center: 'title', right: 'prev,next today' },
        buttonText: { today: 'Dnes' },
        editable: false,
        events,
        eventDidMount: ({ event, el }) => styleCalendarEvent(event, el),
        eventClick: ({ event }) => {
            const canCancel = event.start >= new Date();
            $('#showReservationUserCalendar').modal('toggle');
            $('#totalCount').text(event.extendedProps.totalCount ?? '');
            $('#date').text(event.extendedProps.date ?? '');
            $('#hasChildren').text(event.extendedProps.hasChildren ?? '');
            $('#comment').text(event.extendedProps.comment ?? '');
            cancelButton.data('reservationId', canCancel ? event.id : 0);
            cancelButton.prop('disabled', !canCancel);
        },
        windowResize: ({ view }) => {
            const target = isMobileViewport() ? 'listMonth' : 'dayGridMonth';
            if (view.type !== target) {
                calendar.changeView(target);
                calendar.setOption('height', isMobileViewport() ? 'auto' : 500);
            }
        }
    });
    calendar.render();
}

$(function () {
    if (typeof $.nette !== 'undefined') {
        $.nette.ext('oaza-errors', {
            error: function (xhr) {
                var msg = (xhr && xhr.status) ? 'Chyba ' + xhr.status + ': Požadavek selhal.' : 'Nastala chyba. Zkuste to prosím znovu.';
                notifyMessage(msg, 'error');
            }
        });
    }

    bindCommonUi();
    initPublicMobileNav();

    const calendarElement = document.getElementById('calendar');
    if (!calendarElement || typeof FullCalendar === 'undefined') {
        return;
    }

    if (calendarElement.dataset.calendarKind === 'public') {
        initPublicReservationCalendar(calendarElement);
    }

    if (calendarElement.dataset.calendarKind === 'user') {
        initUserCalendar(calendarElement);
    }
});
