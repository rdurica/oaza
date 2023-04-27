<?php

declare(strict_types=1);

namespace App\Component\Form\Auth\ChangePassword;

interface IChangePassword
{
    public function create(): ChangePassword;
}
