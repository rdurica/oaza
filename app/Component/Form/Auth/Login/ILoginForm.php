<?php

declare(strict_types=1);

namespace App\Component\Form\Auth\Login;

interface ILoginForm
{
    public function create(): LoginForm;
}
