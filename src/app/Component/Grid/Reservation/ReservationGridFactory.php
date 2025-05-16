<?php declare(strict_types=1);

namespace App\Component\Grid\Reservation;

/**
 * Factory for reservation grid.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
interface ReservationGridFactory
{
    /**
     * Create grid.
     *
     * @return Reservation
     */
    public function create(): Reservation;
}
