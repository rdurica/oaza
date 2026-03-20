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
    initPublicMobileNav();

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
