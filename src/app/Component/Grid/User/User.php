<?php declare(strict_types=1);

namespace App\Component\Grid\User;

use App\Component\Component;
use App\Model\Manager\UserManager;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Exception\DatagridException;
use Contributte\Translation\Translator;

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
        public readonly Translator $translator,
    )
    {
    }

    /**
     * Create grid.
     *
     * @return Datagrid
     * @throws DatagridException
     */
    public function createComponentGrid(): Datagrid
    {
        $grid = new Datagrid();
        $grid->setDataSource($this->userManager->getEntityTable());
        $grid->addColumnText('name', 'Jméno')
            ->setFilterText();
        $grid->addColumnText('email', 'E-Mail')
            ->setFilterText();
        $grid->addColumnText('telephone', 'Telefon')
            ->setFilterText();
        $grid->addColumnDateTime('registered', 'Datum registrace')
            ->setFormat('j. n. Y');
        $grid->addColumnText('role', 'Role')
            ->setRenderer(fn($item): string => $item->role === 'admin'
                ? $this->translator->trans('roleAdmin')
                : $this->translator->trans('roleUser'));
        $grid->addAction('edit', 'Upravit', 'Users:Edit')
            ->setClass('btn btn-info btn-xs')
            ->setIcon('pencil');

        return $grid;
    }
}
