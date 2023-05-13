<?php

namespace App\Modules\Admin\Component\Grid\User;

/**
 * User grid interface.
 *
 * @package   App\Modules\Admin\Component\Grid\User
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
interface IUser
{
    public function create(): User;
}
