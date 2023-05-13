<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenter;

use App\Modules\Admin\Component\Grid\User\IUser;
use App\Modules\Admin\Component\Grid\User\User;
use Nette\DI\Attributes\Inject;

/**
 * UsersPresenter
 *
 * @package   App\Modules\Admin\Presenter
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class UsersPresenter extends AdminPresenter
{
    #[Inject]
    public IUser $userGrid;

    /**
     * Create user grid.
     *
     * @return User
     */
    protected function createComponentGrid(): User
    {
        return $this->userGrid->create();
    }
}
