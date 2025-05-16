<?php declare(strict_types=1);

namespace App\Component\Grid\User;

/**
 * Factory for user grid.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
interface UserGridFactory
{
    /**
     * Create grid.
     *
     * @return User
     */
    public function create(): User;
}
