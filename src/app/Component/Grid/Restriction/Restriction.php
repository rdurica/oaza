<?php

declare(strict_types=1);

namespace App\Component\Grid\Restriction;

use App\Component\Component;
use App\Exception\DeleteRestrictionException;
use App\Facade\RestrictionFacade;
use App\Model\Manager\RestrictionManager;
use App\Util\FlashType;
use App\Util\RestrictionSlotResolver;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Exception\DatagridException;
use Contributte\Translation\Translator;
use Nette\Application\AbortException;
use Nette\Utils\DateTime;
use Nette\Utils\Html;

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
     * @param RestrictionFacade $restrictionFacade
     * @param Translator        $translator
     */
    public function __construct(
        private readonly RestrictionManager $restrictionManager,
        private readonly RestrictionFacade $restrictionFacade,
        private readonly Translator $translator,
    )
    {
    }

    /**
     * Create restriction grid.
     *
     * @return Datagrid
     * @throws DatagridException
     */
    public function createComponentGrid(): Datagrid
    {
        $grid = new Datagrid();
        $grid->setDataSource($this->restrictionManager->findAllActive());
        $grid->addColumnText('from', 'Od')
            ->setRenderer(fn ($item): string => $this->formatBound($item->from, $item->to, true));
        $grid->addColumnText('to', 'Do')
            ->setRenderer(fn ($item): string => $this->formatBound($item->from, $item->to, false));
        $grid->addColumnText('message', 'Zpráva')
            ->setRenderer(renderer: fn($item): Html => Html::el()->setHtml($item->message));
        $grid->addAction('edit', 'Upravit', 'Restrictions:Edit')
            ->setIcon('pencil')
            ->setClass('btn btn-info btn-xs');
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
     * @throws DeleteRestrictionException
     */
    public function handleDelete(int $id): void
    {
        $this->restrictionFacade->delete($id);
        $this->getPresenter()->flashMessage($this->translator->trans('flash.restrictionDeleted'), FlashType::SUCCESS);
        $this->getPresenter()->redirect('Restrictions:');
    }

    private function formatBound(mixed $fromValue, mixed $toValue, bool $isFrom): string
    {
        $from = DateTime::from($fromValue);
        $to = DateTime::from($toValue);
        $value = $isFrom ? $from : $to;

        if (RestrictionSlotResolver::isDateOnlyRange($from, $to)) {
            return $value->format('j.n.Y');
        }

        return $value->format('j.n.Y H:i');
    }
}
