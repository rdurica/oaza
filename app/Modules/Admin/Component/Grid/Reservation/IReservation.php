<?php

declare(strict_types=1);

namespace App\Modules\Admin\Component\Grid\Reservation;

/**
 * Reservation grid interface.
 *
 * @package   App\Modules\Admin\Component\Grid\Reservation
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
interface IReservation
{
    public function create(): Reservation;
}
