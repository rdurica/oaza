<?php

declare(strict_types=1);

namespace App\Presenter;

use App\Component\Form\Auth\ChangePassword\ChangePassword;
use App\Component\Form\Auth\ChangePassword\ChangePasswordFormFactory;
use App\Exception\NotAllowedOperationException;
use App\Model\Service\CalendarServiceOld;
use App\Model\Service\ReservationServiceOld;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Nette\Application\AbortException;
use Nette\DI\Attributes\Inject;

/**
 * UserPresenter
 *
 * @package   App\Presenter
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class UserPresenter extends SecurePresenter
{
    #[Inject]
    public CalendarServiceOld $calendarService;

    #[Inject]
    public ReservationServiceOld $reservationService;

    #[Inject]
    public ChangePasswordFormFactory $changePasswordForm;

    #[Inject]
    public Translator $translator;


    /**
     * Render calendar page.
     *
     * @return void
     */
    public function renderCalendar(): void
    {
        $this->getTemplate()->data = $this->calendarService->getUserData();
    }


    /**
     * Cancel reservation.
     *
     * @param $reservationId
     * @return void
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
     * Create change password form.
     *
     * @return ChangePassword
     */
    public function createComponentChangePasswordForm(): ChangePassword
    {
        return $this->changePasswordForm->create();
    }
}
