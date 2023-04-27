<?php

declare(strict_types=1);

namespace App\Component\Form\Auth\Register;

interface IRegistrationForm
{
    public function create(): RegistrationForm;
}
