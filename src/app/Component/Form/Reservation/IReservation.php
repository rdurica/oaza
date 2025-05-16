<?php

declare(strict_types=1);

namespace App\Component\Form\Reservation;

/**
 * Reservation form interface.
 *
 * @package   App\Component\Form\Reservation
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
interface IReservation
{
    public function create(): Reservation;
}
