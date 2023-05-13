<?php

declare(strict_types=1);

namespace App\Component\Form\Auth\ResetPassword;

/**
 * ResetPassword form interface.
 *
 * @package   App\Component\Form\Auth\ResetPassword
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
interface IResetPassword
{
    public function create(): ResetPassword;
}
