<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenter;

use App\Component\Form\ReservationEdit\ReservationEdit;
use App\Component\Form\ReservationEdit\ReservationEditFormFactory;
use App\Component\Grid\Reservation\ReservationGridFactory;
use App\Component\Grid\Reservation\Reservation;
use App\Exception\NotAllowedOperationException;
use App\Model\Manager\ReservationManager;
use App\Model\Service\ReservationService;
use App\Util\FlashType;
use Nette\Application\AbortException;
use Nette\DI\Attributes\Inject;
use Nette\Mail\SmtpException;

/**
 * ReservationsPresenter
 *
 * @package   App\Modules\Admin\Presenter
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class ReservationsPresenter extends AdminPresenter
{
    #[Inject]
    public ReservationGridFactory $reservationGrid;

    #[Inject]
    public ReservationEditFormFactory $reservationEditForm;

    #[Inject]
    public ReservationManager $reservationManager;

    #[Inject]
    public ReservationService $reservationService;

    public ?int $reservationId = null;

    /**
     * Create reservation grid.
     *
     * @param string $name
     * @return Reservation
     */
    protected function createComponentGrid(string $name): Reservation
    {
        return $this->reservationGrid->create();
    }

    /**
     * Create reservation edit form.
     *
     * @return ReservationEdit
     */
    protected function createComponentForm(): ReservationEdit
    {
        return $this->reservationEditForm->create($this->reservationId);
    }

    /**
     * Edit reservation.
     *
     * @param int $id
     *
     * @return void
     * @throws AbortException
     */
    public function renderEdit(int $id): void
    {
        $this->reservationId = $id;
        $this->template->id = $id;
        $data = $this->reservationManager->findById($id);

        if ($data === null) {
            $this->flashMessage($this->translator->trans('flash.operationNotAllowed'), FlashType::WARNING);
            $this->redirect('Reservations:');
        }
    }

    /**
     * Cancel reservation.
     *
     * @param int $id
     *
     * @return void
     * @throws AbortException
     */
    public function handleCancelReservation(int $id): void
    {
        try {
            $this->reservationService->cancelByAdmin($id);
            $this->flashMessage($this->translator->trans('flash.reservationDeleted'), FlashType::SUCCESS);
        } catch (SmtpException) {
            $this->flashMessage('Nastal problém při odesílání potvrzovacího e-mailu.', FlashType::WARNING);
        } catch (NotAllowedOperationException) {
            $this->flashMessage($this->translator->trans('flash.operationNotAllowed'), FlashType::ERROR);
        } catch (\Exception) {
            $this->flashMessage($this->translator->trans('flash.oops'), FlashType::ERROR);
        }

        $this->redirect('Reservations:');
    }
}
