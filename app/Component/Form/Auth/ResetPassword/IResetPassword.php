<?php

declare(strict_types=1);

namespace App\Component\Form\Auth\ResetPassword;

interface IResetPassword
{
    public function create(): ResetPassword;
}
