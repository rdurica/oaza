<?php declare(strict_types=1);

namespace App\Component\Form\News;

/**
 * Factory for news form.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
interface NewsFormFactory
{
    /**
     * Create form.
     *
     * @param int|null $id
     *
     * @return News
     */
    public function create(?int $id): News;
}
