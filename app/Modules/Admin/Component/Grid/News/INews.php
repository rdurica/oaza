<?php

declare(strict_types=1);

namespace App\Modules\Admin\Component\Grid\News;

/**
 * News grid interface.
 *
 * @package   App\Modules\Admin\Component\Grid\News
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
interface INews
{
    public function create(): News;
}
