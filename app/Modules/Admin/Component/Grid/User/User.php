<?php

namespace App\Modules\Admin\Component\Grid\User;

use App\Component\Component;
use App\Model\Manager\UserManager;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\AbortException;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

class User extends Component
{
    public function __construct(public readonly UserManager $userManager, public readonly Translator $translator)
    {
    }


    /**
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
     * Change enabled/disabled handler
     * @throws AbortException
     */
    #[NoReturn] public function handleStatus(int $id): void
    {
        $this->userManager->changeStatus($id);
        $this->getPresenter()->flashMessage($this->translator->trans("flash.userUpdated"), FlashType::INFO);
        $this->getPresenter()->redirect('this');
    }


    /**
     * Delete user handler
     * @throws AbortException
     */
    #[NoReturn] public function handleDelete(int $id): void
    {
        $this->userManager->deleteById($id);
        $this->getPresenter()->flashMessage($this->translator->trans("flash.userDeleted"), FlashType::INFO);
        $this->getPresenter()->redirect('this');
    }
}
