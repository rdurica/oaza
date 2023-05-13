<?php

declare(strict_types=1);

namespace App\Modules\Admin\Component\Form\News;

/**
 * News form interface.
 *
 * @package   App\Modules\Admin\Component\Form\News
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
interface INews
{
    public function create(?int $id): News;
}
