<?php declare(strict_types=1);

namespace App\Component\Form\Auth\ChangePassword;

/**
 * Factory method for change password form.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
interface ChangePasswordFormFactory
{
    /**
     * Create form.
     *
     * @return ChangePassword
     */
    public function create(): ChangePassword;
}
