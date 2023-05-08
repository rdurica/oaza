<?php

declare(strict_types=1);

namespace App\Modules\Admin\Component\Grid\Restriction;

interface IRestriction
{
    public function create(): Restriction;
}
