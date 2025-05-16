<?php declare(strict_types=1);

namespace App\Component\Form\Auth\Login;

/**
 * Factory for login form.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
interface LoginFormFactory
{
    /**
     * Create form.
     *
     * @return Login
     */
    public function create(): Login;
}
