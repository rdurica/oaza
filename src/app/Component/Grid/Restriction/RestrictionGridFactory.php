<?php

declare(strict_types=1);

namespace App\Component\Grid\Restriction;

/**
 * Factory for restrictions grid.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
interface RestrictionGridFactory
{
    /**
     * Create grid.
     *
     * @return Restriction
     */
    public function create(): Restriction;
}
