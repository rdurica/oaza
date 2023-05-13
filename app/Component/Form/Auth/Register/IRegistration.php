<?php

declare(strict_types=1);

namespace App\Component\Form\Auth\Register;

/**
 * Registration form interface.
 *
 * @package   App\Component\Form\Auth\Register
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
interface IRegistration
{
    public function create(): Registration;
}
