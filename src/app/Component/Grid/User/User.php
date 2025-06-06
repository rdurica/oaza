<?php declare(strict_types=1);

namespace App\Component\Grid\User;

use App\Component\Component;
use App\Model\Manager\UserManager;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Exception;
use Nette\Application\AbortException;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * User grid.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
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
        public readonly Translator $translator
    )
    {
    }

    /**
     * Create grid.
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
            ->setFormat('j. n. Y');
        $grid->addColumnText('enabled', 'Status')
            ->setRenderer(fn($item): string => ($item->enabled === 1) ? 'Povolen' : 'Zakazan');
        $grid->addAction('send', 'status', 'status!')
            ->setClass('btn btn-info btn-xs')
            ->setIcon('eye');

        return $grid;
    }

    /**
     * Action change status (Enabled/Disabled)
     *
     * @param int $id
     *
     * @return void
     * @throws AbortException
     */
    public function handleStatus(int $id): void
    {
        try
        {
            $this->userManager->changeStatus($id);
            $this->getPresenter()->flashMessage($this->translator->trans('flash.userUpdated'), FlashType::INFO);
        }
        catch (Exception)
        {
            $this->getPresenter()->flashMessage($this->translator->trans('flash.oops'), FlashType::ERROR);
        }

        $this->getPresenter()->redirect('this');
    }
}
