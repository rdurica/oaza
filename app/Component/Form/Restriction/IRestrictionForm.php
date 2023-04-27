<?php

declare(strict_types=1);

namespace App\Component\Form\Restriction;

interface IRestrictionForm
{
    public function create(): RestrictionForm;
}
