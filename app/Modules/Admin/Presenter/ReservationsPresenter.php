<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenter;

use App\Modules\Admin\Component\Grid\Reservation\IReservation;
use App\Modules\Admin\Component\Grid\Reservation\Reservation;
use Nette\DI\Attributes\Inject;

class ReservationsPresenter extends AdminPresenter
{
    #[Inject]
    public IReservation $reservationGrid;

    protected function createComponentGrid(string $name): Reservation
    {
        return $this->reservationGrid->create();
    }
}
