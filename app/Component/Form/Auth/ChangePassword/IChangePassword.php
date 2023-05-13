<?php

declare(strict_types=1);

namespace App\Component\Form\Auth\ChangePassword;

/**
 * ChangePassword form interface.
 *
 * @package   App\Component\Form\Auth\ChangePassword
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
interface IChangePassword
{
    public function create(): ChangePassword;
}
