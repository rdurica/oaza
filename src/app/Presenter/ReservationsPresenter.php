<?php declare(strict_types=1);

namespace App\Presenter;

use App\Component\Form\Reservation\Reservation;
use App\Component\Form\Reservation\ReservationFormFactory;
use App\Model\Service\CalendarServiceOld;

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
     * @param CalendarServiceOld     $calendarService
     * @param ReservationFormFactory $reservationFormFactory
     */
    public function __construct(private readonly CalendarServiceOld $calendarService, private readonly ReservationFormFactory $reservationFormFactory)
    {
        parent::__construct();
    }

    /**
     * Create reservation page.
     *
     * @return void
     */
    public function renderCreate(): void
    {
        $this->getTemplate()->data = $this->calendarService->getData();
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
