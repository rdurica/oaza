<?php declare(strict_types=1);

namespace App\Component\Grid\Restriction;

use App\Component\Component;
use App\Model\Manager\RestrictionManager;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Nette\Application\AbortException;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * Restriction grid.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
class Restriction extends Component
{
    /**
     * Constructor.
     *
     * @param RestrictionManager $restrictionManager
     * @param Translator         $translator
     */
    public function __construct(
        private readonly RestrictionManager $restrictionManager,
        private readonly Translator $translator,
    )
    {
    }

    /**
     * Create restriction grid.
     *
     * @return DataGrid
     * @throws DataGridException
     */
    public function createComponentGrid(): DataGrid
    {
        $grid = new DataGrid();
        $grid->setDataSource($this->restrictionManager->findAllActive());
        $grid->addColumnDateTime('from', 'Od')
            ->setFormat('j.n.Y', 'd. m. yyyy');
        $grid->addColumnDateTime('to', 'Do')
            ->setFormat('j.n.Y', 'd. m. yyyy');
        $grid->addColumnText('message', 'Zpráva')
            ->setRenderer(renderer: fn($item): Html => Html::el()->setHtml($item->message));
        $grid->addAction('delete', 'Smazat', 'delete!')
            ->setIcon('trash')
            ->setClass('btn btn-danger btn-xs');

        return $grid;
    }

    /**
     * Action delete restriction.
     *
     * @param int $id
     *
     * @return void
     * @throws AbortException
     */
    public function handleDelete(int $id): void
    {
        $this->restrictionManager->delete($id);
        $this->getPresenter()->flashMessage($this->translator->trans('flash.restrictionDeleted'), FlashType::SUCCESS);
        $this->getPresenter()->redirect('Restrictions:');
    }
}
