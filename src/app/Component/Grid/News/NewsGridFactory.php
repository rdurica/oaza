<?php declare(strict_types=1);

namespace App\Component\Grid\News;

/**
 * Factory for news grid.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
interface NewsGridFactory
{
    /**
     * Create grid.
     *
     * @return News
     */
    public function create(): News;
}
