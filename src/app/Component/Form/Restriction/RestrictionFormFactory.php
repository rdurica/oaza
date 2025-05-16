<?php declare(strict_types=1);

namespace App\Component\Form\Restriction;

/**
 * Factory for restriction form.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
interface RestrictionFormFactory
{
    /**
     * Create form.
     *
     * @return Restriction
     */
    public function create(): Restriction;
}
