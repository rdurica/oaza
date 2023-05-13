<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenter;

use App\Modules\Admin\Component\Grid\Reservation\IReservation;
use App\Modules\Admin\Component\Grid\Reservation\Reservation;
use Nette\DI\Attributes\Inject;

/**
 * ReservationsPresenter
 *
 * @package   App\Modules\Admin\Presenter
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class ReservationsPresenter extends AdminPresenter
{
    #[Inject]
    public IReservation $reservationGrid;

    /**
     * Create reservation grid.
     *
     * @param string $name
     * @return Reservation
     */
    protected function createComponentGrid(string $name): Reservation
    {
        return $this->reservationGrid->create();
    }
}
