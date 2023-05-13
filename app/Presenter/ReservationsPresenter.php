<?php

declare(strict_types=1);

namespace App\Presenter;

use App\Component\Form\Reservation\IReservation;
use App\Component\Form\Reservation\Reservation;
use App\Model\Service\CalendarServiceOld;
use Nette\DI\Attributes\Inject;

/**
 * ReservationsPresenter
 *
 * @package   App\Presenter
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class ReservationsPresenter extends Presenter
{
    #[Inject]
    public CalendarServiceOld $calendarService;

    #[Inject]
    public IReservation $reservationForm;


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
        return $this->reservationForm->create();
    }
}
