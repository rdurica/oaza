<?php declare(strict_types=1);

namespace App\Component\Form\Auth\ResetPassword;

/**
 * Factory for reset password form.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
interface ResetPasswordFormFactory
{
    /**
     * Create form.
     *
     * @return ResetPassword
     */
    public function create(): ResetPassword;
}
