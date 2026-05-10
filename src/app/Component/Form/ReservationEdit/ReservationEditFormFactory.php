<?php

declare(strict_types=1);

namespace App\Component\Form\ReservationEdit;

/**
 * Factory for reservation edit form.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
interface ReservationEditFormFactory
{
    /**
     * Create form.
     *
     * @param int|null $reservationId
     *
     * @return ReservationEdit
     */
    public function create(?int $reservationId): ReservationEdit;
}
