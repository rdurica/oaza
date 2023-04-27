<?php

namespace App\Presenter;

use App\Component\Form\Reservation\IReservation;
use App\Model\Service\CalendarService;

/**
 * Class ReservationsPresenter
 * @package App\Presenters
 */
class ReservationsPresenter extends Presenter
{
    /** @var  CalendarService @inject */
    public $calendarService;

    /** @var  IReservation @inject */
    public $reservationForm;


    /**
     * Render Create
     */
    public function renderCreate()
    {
        $resault = $this->calendarService->getData();
        $this->template->data = $resault;
    }


    /**
     * @return \Oaza\Forms\Reservation
     */
    public function createComponentRezervationForm()
    {
        return $this->reservationForm->create();
    }
}
