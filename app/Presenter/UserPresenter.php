<?php

declare(strict_types=1);

namespace App\Presenter;

use App\Component\Form\Auth\ChangePassword\ChangePassword;
use App\Component\Form\Auth\ChangePassword\IChangePassword;
use App\Exception\NotAllowedOperationException;
use App\Model\Service\CalendarService;
use App\Model\Service\ReservationService;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Nette\Application\AbortException;
use Nette\DI\Attributes\Inject;

class UserPresenter extends SecurePresenter
{
    #[Inject]
    public CalendarService $calendarService;

    #[Inject]
    public ReservationService $reservationService;

    #[Inject]
    public IChangePassword $changePassword;

    #[Inject]
    public Translator $translator;


    public function renderCalendar(): void
    {
        /** Calendar data */
        $this->getTemplate()->data = $this->calendarService->getUserData();
    }


    /**
     * Handler for cancel reservation
     * @throws AbortException
     */
    public function handleCancelReservation($reservationId): void
    {
        try {
            $this->reservationService->delete((int)$reservationId);
            $this->presenter->flashMessage($this->translator->trans("flash.reservationDeleted"), FlashType::INFO);
        } catch (NotAllowedOperationException $e) {
            $this->presenter->flashMessage($this->translator->trans("flash.operationNotAllowed"), FlashType::ERROR);
        }

        $this->getPresenter()->redirect('this');
    }


    /**
     * Form
     */
    public function createComponentChangePassword(): ChangePassword
    {
        return $this->changePassword->create();
    }
}
