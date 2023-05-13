<?php

namespace App\Modules\Admin\Component\Grid\User;

use App\Component\Component;
use App\Model\Manager\UserManager;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Nette\Application\AbortException;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * User grid.
 *
 * @package   App\Modules\Admin\Component\Grid\User
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class User extends Component
{
    /**
     * Constructor.
     *
     * @param UserManager $userManager
     * @param Translator  $translator
     */
    public function __construct(
        public readonly UserManager $userManager,
        public readonly Translator  $translator
    )
    {
    }


    /**
     * Create user grid.
     *
     * @return DataGrid
     * @throws DataGridException
     */
    public function createComponentGrid(): DataGrid
    {
        $grid = new DataGrid();
        $grid->setDataSource($this->userManager->getEntityTable());
        $grid->addColumnText('name', 'Jméno')
            ->setFilterText();
        $grid->addColumnText('email', 'E-Mail')
            ->setFilterText();
        $grid->addColumnText('telephone', 'Telefon')
            ->setFilterText();
        $grid->addColumnDateTime('registered', 'Datum registrace')
            ->setFormat('j. n. Y', 'd. m. yyyy');
        $grid->addColumnText('enabled', 'Status')
            ->setRenderer(function ($item): string {
                if ($item->enabled === 1) {
                    return 'Povolen';
                } else {
                    return 'Zakazan';
                }
            });
        $grid->addAction('delete', 'Smazat', 'delete!')
            ->setIcon('trash')
            ->setClass('btn btn-danger btn-xs');
        $grid->addAction('send', 'status', 'status!')
            ->setClass('btn btn-info btn-xs')
            ->setIcon('eye');

        return $grid;
    }


    /**
     * Action change status (Enabled/Disabled)
     *
     * @param int $id
     * @return void
     * @throws AbortException
     */
    public function handleStatus(int $id): void
    {
        $this->userManager->changeStatus($id);
        $this->getPresenter()->flashMessage($this->translator->trans("flash.userUpdated"), FlashType::INFO);
        $this->getPresenter()->redirect('this');
    }


    /**
     * Action delete user
     *
     * @param int $id
     * @return void
     * @throws AbortException
     */
    public function handleDelete(int $id): void
    {
        $this->userManager->delete($id);
        $this->getPresenter()->flashMessage($this->translator->trans("flash.userDeleted"), FlashType::INFO);
        $this->getPresenter()->redirect('this');
    }
}
