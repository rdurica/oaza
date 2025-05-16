<?php declare(strict_types=1);

namespace App\Component\Form\ContactUs;

/**
 * Factory method for contact us form.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
interface ContactUsFormFactory
{
    /**
     * Create form.
     *
     * @return ContactUs
     */
    public function create(): ContactUs;
}
