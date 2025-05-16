<?php declare(strict_types=1);

namespace App\Presenter;

use App\Component\Form\Auth\ChangePassword\ChangePassword;
use App\Component\Form\Auth\ChangePassword\ChangePasswordFormFactory;
use App\Exception\NotAllowedOperationException;
use App\Model\Service\CalendarServiceOld;
use App\Model\Service\ReservationServiceOld;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Exception;
use Nette\Application\AbortException;
use Nette\Mail\SmtpException;

/**
 * UserPresenter.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
class UserPresenter extends SecurePresenter
{
    /**
     * Constructor.
     *
     * @param CalendarServiceOld        $calendarService
     * @param ReservationServiceOld     $reservationService
     * @param ChangePasswordFormFactory $changePasswordFormFactory
     * @param Translator                $translator
     */
    public function __construct(
        private readonly CalendarServiceOld $calendarService,
        private readonly ReservationServiceOld $reservationService,
        private readonly ChangePasswordFormFactory $changePasswordFormFactory,
        public Translator $translator,
    )
    {
        parent::__construct();
    }

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
     *
     * @return void
     * @throws AbortException
     */
    public function handleCancelReservation($reservationId): void
    {
        try
        {
            $this->reservationService->delete((int)$reservationId);
            $this->presenter->flashMessage($this->translator->trans('flash.reservationDeleted'), FlashType::INFO);
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

        $this->getPresenter()->redirect('this');
    }

    /**
     * Create change password form.
     *
     * @return ChangePassword
     */
    public function createComponentChangePasswordForm(): ChangePassword
    {
        return $this->changePasswordFormFactory->create();
    }
}
