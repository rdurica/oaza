<?php

declare(strict_types=1);

namespace App\Modules\Admin\Component\Grid\Reservation;

use App\Component\Component;
use App\Exception\NotAllowedOperationException;
use App\Model\Manager\ReservationManager;
use App\Model\Service\ReservationService;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\AbortException;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

class Reservation extends Component
{
    public function __construct(
        public readonly ReservationManager $reservationManager,
        public readonly ReservationService $reservationService,
        public readonly Translator $translator,
    ) {
    }

    /**
     * @throws DataGridException
     */
    public function createComponentGrid(): DataGrid
    {
        $grid = new DataGrid();

        $grid->setDataSource($this->reservationManager->findAllActive());
        $grid->addColumnText('name', 'Jméno')
            ->setFilterText();
        $grid->addColumnText('email', 'E-Mail')
            ->setFilterText();
        $grid->addColumnText('telephone', 'Telefon')
            ->setFilterText();
        $grid->addColumnText('count', 'Pocet mist');
        $grid->addColumnDateTime('reservationDate', 'Rezervace')
            ->setFormat('j.n.Y H:i', 'd. m. yyyy');
        $grid->addColumnText('children', 'Děti');
        $grid->addAction('cancel', 'Zrusit', 'cancelReservation!')
            ->setIcon('trash')
            ->setClass('btn btn-danger btn-xs')
            ->setConfirmation(
                new StringConfirmation('Naozaj chcete zrusit rezervaci %s??', 'reservationDate')
            );

        return $grid;
    }

    /**
     * @throws NotAllowedOperationException
     * @throws AbortException
     */
    #[NoReturn] public function handleCancelReservation(int $id): void
    {
        $this->reservationService->delete($id, true);
        $this->getPresenter()->flashMessage($this->translator->trans("flash.reservationDeleted"), FlashType::SUCCESS);
        $this->getPresenter()->redirect('Reservations:');
    }
}
