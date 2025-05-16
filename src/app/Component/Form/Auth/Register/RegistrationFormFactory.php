<?php declare(strict_types=1);

namespace App\Component\Form\Auth\Register;

/**
 * Factory for registration form.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
interface RegistrationFormFactory
{
    /**
     * Create form.
     *
     * @return Registration
     */
    public function create(): Registration;
}
