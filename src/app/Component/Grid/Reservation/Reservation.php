<?php declare(strict_types=1);

namespace App\Component\Grid\Reservation;

use App\Component\Component;
use App\Exception\NotAllowedOperationException;
use App\Model\Manager\ReservationManager;
use App\Model\Service\ReservationServiceOld;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Exception;
use Nette\Mail\SmtpException;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

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
     * @param ReservationManager    $reservationManager
     * @param ReservationServiceOld $reservationService
     * @param Translator            $translator
     */
    public function __construct(
        public readonly ReservationManager $reservationManager,
        public readonly ReservationServiceOld $reservationService,
        public readonly Translator $translator,
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

        $grid->setDataSource($this->reservationManager->findAllActive());
        $grid->addColumnText('name', 'Jméno')
            ->setFilterText();
        $grid->addColumnText('email', 'E-Mail')
            ->setFilterText();
        $grid->addColumnText('telephone', 'Telefon')
            ->setFilterText();
        $grid->addColumnText('count', 'Pocet mist');
        $grid->addColumnDateTime('reservationDate', 'Rezervace')
            ->setFormat('j.n.Y H:i');
        $grid->addColumnText('children', 'Děti');
        $grid->addAction('cancel', 'Zrusit', 'cancelReservation!')
            ->setIcon('trash')
            ->setClass('btn btn-danger btn-xs')
            ->setConfirmation(new StringConfirmation('Naozaj chcete zrusit rezervaci %s??', 'reservationDate'));

        return $grid;
    }

    /**
     * Action cancel reservation.
     *
     * @param int $id
     *
     * @return void
     */
    public function handleCancelReservation(int $id): void
    {
        try
        {
            $this->reservationService->delete($id, true);
            $this->getPresenter()->flashMessage($this->translator->trans('flash.reservationDeleted'), FlashType::SUCCESS);
        }
        catch (SmtpException)
        {
            $this->getPresenter()->flashMessage(
                'Nastal problém při odesílání potvrzovacího e-mailu.',
                FlashType::WARNING
            );
        }
        catch (NotAllowedOperationException)
        {
            $this->presenter->flashMessage($this->translator->trans('flash.operationNotAllowed'), FlashType::ERROR);
        }
        catch (Exception)
        {
            $this->presenter->flashMessage($this->translator->trans('flash.oops'), FlashType::ERROR);
        }

        $this->getPresenter()->redirect('Reservations:');
    }
}
