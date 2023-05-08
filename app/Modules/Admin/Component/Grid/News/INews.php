<?php

declare(strict_types=1);

namespace App\Modules\Admin\Component\Grid\News;

interface INews
{
    public function create(): News;
}
