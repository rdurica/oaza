<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenter;

use App\Component\Form\UserEdit\UserEdit;
use App\Component\Form\UserEdit\UserEditFormFactory;
use App\Component\Grid\User\User;
use App\Component\Grid\User\UserGridFactory;
use App\Model\Manager\UserManager;
use App\Util\FlashType;
use Nette\Application\AbortException;
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
    public UserGridFactory $userGrid;

    #[Inject]
    public UserEditFormFactory $userEditForm;

    #[Inject]
    public UserManager $userManager;

    public ?int $userId = null;

    /**
     * Create user grid.
     *
     * @return User
     */
    protected function createComponentGrid(): User
    {
        return $this->userGrid->create();
    }

    /**
     * Create user edit form.
     *
     * @return UserEdit
     */
    protected function createComponentForm(): UserEdit
    {
        return $this->userEditForm->create($this->userId);
    }

    /**
     * Edit user (validate if exact user exists)
     *
     * @param int $id
     * @return void
     * @throws AbortException
     */
    public function renderEdit(int $id): void
    {
        $this->userId = $id;
        $data = $this->userManager->getById($id);

        if (!$data) {
            $this->getPresenter()
                ->flashMessage($this->translator->trans('flash.userDoesNotExist'), FlashType::WARNING);
            $this->getPresenter()->redirect('Users:');
        }
    }
}
