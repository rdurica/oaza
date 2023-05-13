<?php

declare(strict_types=1);

namespace App\Component\Form\ContactUs;

/**
 * ContactUs form interface.
 *
 * @package   App\Component\Form\ContactUs
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
interface IContactUs
{
    public function create(): ContactUs;
}
