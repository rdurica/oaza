<?php

declare(strict_types=1);

namespace App\Modules\Admin\Component\Form\News;

interface INews
{
    public function create(?int $id): News;
}
