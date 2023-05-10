<?php

declare(strict_types=1);

namespace App\Modules\Admin\Component\Grid\Restriction;

use App\Component\Component;
use App\Model\Manager\RestrictionManager;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\AbortException;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

class Restriction extends Component
{
    public function __construct(
        private readonly RestrictionManager $restrictionManager,
        private readonly Translator $translator,
    ) {
    }


    /**
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
            ->setRenderer(renderer: function ($item): Html {
                return Html::el()->setHtml($item->message);
            });
        $grid->addAction('delete', 'Smazat', 'delete!')
            ->setIcon('trash')
            ->setClass('btn btn-danger btn-xs');

        return $grid;
    }

    /**
     * @throws AbortException
     */
    #[NoReturn] public function handleDelete(int $id): void
    {
        $this->restrictionManager->deleteById($id);
        $this->getPresenter()->flashMessage($this->translator->trans("flash.restrictionDeleted"), FlashType::SUCCESS);
        $this->getPresenter()->redirect('Restrictions:');
    }
}
