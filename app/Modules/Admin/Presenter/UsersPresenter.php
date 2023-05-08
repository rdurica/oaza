<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenter;

use App\Modules\Admin\Component\Grid\User\IUser;
use App\Modules\Admin\Component\Grid\User\User;
use Nette\DI\Attributes\Inject;

class UsersPresenter extends AdminPresenter
{
    #[Inject]
    public IUser $userGrid;

    protected function createComponentGrid(): User
    {
        return $this->userGrid->create();
    }
}
