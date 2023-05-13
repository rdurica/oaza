<?php

declare(strict_types=1);

namespace App\Component\Form\Auth\Login;

/**
 * Login form interface.
 *
 * @package   App\Component\Form\Auth\Login
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
interface ILogin
{
    public function create(): Login;
}
