<?php declare(strict_types=1);

namespace App\Component\Form\Reservation;

/**
 * Factory for reservation form.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
interface ReservationFormFactory
{
    /**
     * Create form.
     *
     * @return Reservation
     */
    public function create(): Reservation;
}
