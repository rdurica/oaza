<?php

declare(strict_types=1);

namespace App\Modules\Admin\Component\Grid\Restriction;

/**
 * Restriction grid interface.
 *
 * @package   App\Modules\Admin\Component\Grid\Restriction
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
interface IRestriction
{
    public function create(): Restriction;
}
