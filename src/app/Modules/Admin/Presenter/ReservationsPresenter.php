<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenter;

use App\Component\Grid\Reservation\ReservationGridFactory;
use App\Component\Grid\Reservation\Reservation;
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
    public ReservationGridFactory $reservationGrid;

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
