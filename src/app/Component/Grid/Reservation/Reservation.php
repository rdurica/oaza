<?php

declare(strict_types=1);

namespace App\Component\Grid\Reservation;

use App\Component\Component;
use App\Model\Manager\ReservationManager;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Exception\DatagridException;
use Contributte\Translation\Translator;

/**
 * Reservation grid.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
class Reservation extends Component
{
    /**
     * Constructor.
     *
     * @param ReservationManager $reservationManager
     * @param Translator         $translator
     * @param string             $mode
     */
    public function __construct(
        public readonly ReservationManager $reservationManager,
        public readonly Translator $translator,
        private readonly string $mode = ReservationGridFactory::MODE_UPCOMING,
    ) {
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

        $isHistory = $this->mode === ReservationGridFactory::MODE_HISTORY;
        $grid->setDataSource(
            $isHistory
                ? $this->reservationManager->findAllHistory()
                : $this->reservationManager->findAllActive(),
        );
        $grid->addColumnText('name', 'Jméno')
            ->setFilterText();
        $grid->addColumnText('email', 'E-Mail')
            ->setFilterText();
        $grid->addColumnText('telephone', 'Telefon')
            ->setFilterText();
        $grid->addColumnText('count', 'Pocet mist');
        $grid->addColumnDateTime('date', 'Rezervace')
            ->setFormat('j.n.Y H:i');
        $grid->addColumnText('hasChildren', 'Děti');

        if ($isHistory === false) {
            $grid->addAction('edit', 'Upravit', 'Reservations:Edit')
                ->setIcon('pencil')
                ->setClass('btn btn-info btn-xs');
        }

        return $grid;
    }
}
