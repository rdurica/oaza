<?php

declare(strict_types=1);

namespace App\Presenter;

use App\Component\Form\Reservation\Reservation;
use App\Component\Form\Reservation\ReservationFormFactory;
use App\Model\Service\ReservationCalendarService;
use Nette\Utils\Json;

/**
 * ReservationsPresenter.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
class ReservationsPresenter extends Presenter
{
    /**
     * Constructor.
     *
     * @param ReservationCalendarService $calendarService
     * @param ReservationFormFactory $reservationFormFactory
     */
    public function __construct(
        private readonly ReservationCalendarService $calendarService,
        private readonly ReservationFormFactory $reservationFormFactory,
    ) {
        parent::__construct();
    }

    /**
     * Create reservation page.
     *
     * @return void
     */
    public function renderCreate(): void
    {
        $this->getTemplate()->calendarEvents = Json::encode(
            $this->calendarService->getPublicCalendarEvents(),
        );
    }

    /**
     * Create reservation form.
     *
     * @return Reservation
     */
    public function createComponentReservationForm(): Reservation
    {
        return $this->reservationFormFactory->create();
    }
}
