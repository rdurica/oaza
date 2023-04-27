<?php

declare(strict_types=1);

namespace App\Component\Form\Reservation;

interface IReservation
{
    public function create(): Reservation;
}
