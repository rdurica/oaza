<?php

declare(strict_types=1);

namespace App\Component\Form\RestrictionEdit;

/**
 * Factory for restriction edit form.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
interface RestrictionEditFormFactory
{
    /**
     * Create form.
     *
     * @param int|null $restrictionId
     *
     * @return RestrictionEdit
     */
    public function create(?int $restrictionId): RestrictionEdit;
}
