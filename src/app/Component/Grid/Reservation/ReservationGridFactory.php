<?php

declare(strict_types=1);

namespace App\Component\Grid\Reservation;

/**
 * Factory for reservation grid.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
interface ReservationGridFactory
{
    public const string MODE_UPCOMING = 'upcoming';

    public const string MODE_HISTORY = 'history';

    /**
     * Create grid.
     *
     * @param string $mode upcoming|history
     *
     * @return Reservation
     */
    public function create(string $mode = self::MODE_UPCOMING): Reservation;
}
