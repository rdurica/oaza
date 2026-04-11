<?php

declare(strict_types=1);

namespace App\Presenter;

use App\Component\Form\Auth\ChangePassword\ChangePassword;
use App\Component\Form\Auth\ChangePassword\ChangePasswordFormFactory;
use App\Component\Form\Auth\ResetPasswordFromLink\ResetPasswordFromLink;
use App\Component\Form\Auth\ResetPasswordFromLink\ResetPasswordFromLinkFormFactory;
use App\Exception\NotAllowedOperationException;
use App\Model\Manager\PasswordResetTokenManager;
use App\Model\Service\ReservationCalendarService;
use App\Model\Service\ReservationService;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Exception;
use Nette\Mail\SmtpException;
use Nette\Utils\Json;

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
     * @param ReservationCalendarService       $calendarService
     * @param ReservationService               $reservationService
     * @param ChangePasswordFormFactory        $changePasswordFormFactory
     * @param ResetPasswordFromLinkFormFactory $resetPasswordFromLinkFormFactory
     * @param PasswordResetTokenManager        $passwordResetTokenManager
     * @param Translator                       $translator
     */
    public function __construct(
        private readonly ReservationCalendarService $calendarService,
        private readonly ReservationService $reservationService,
        private readonly ChangePasswordFormFactory $changePasswordFormFactory,
        private readonly ResetPasswordFromLinkFormFactory $resetPasswordFromLinkFormFactory,
        private readonly PasswordResetTokenManager $passwordResetTokenManager,
        public Translator $translator,
    ) {
        parent::__construct();
    }

    protected function requiresLogin(): bool
    {
        return $this->action !== 'resetPassword';
    }

    /**
     * Render calendar page.
     *
     * @return void
     */
    public function renderCalendar(): void
    {
        $userId = $this->getUser()->getId();
        if ($userId === null) {
            $this->getTemplate()->calendarEvents = Json::encode([]);
            return;
        }

        $this->getTemplate()->calendarEvents = Json::encode(
            $this->calendarService->getUserCalendarEvents($userId),
        );
    }

    /**
     * Cancel reservation.
     *
     * @return void
     * @throws AbortException
     */
    public function handleCancelReservation(?int $reservationId = null): void
    {
        if ($reservationId === null || $reservationId <= 0) {
            $this->getPresenter()->redirect('this');
            return;
        }

        try {
            $this->reservationService->cancelByUser($reservationId);
            $this->presenter->flashMessage($this->translator->trans('flash.reservationDeleted'), FlashType::INFO);
        } catch (SmtpException) {
            $this->getPresenter()->flashMessage(
                'Nastal problém při odesílání potvrzovacího e-mailu.',
                FlashType::WARNING
            );
        } catch (NotAllowedOperationException) {
            $this->presenter->flashMessage($this->translator->trans('flash.operationNotAllowed'), FlashType::ERROR);
        } catch (Exception) {
            $this->presenter->flashMessage($this->translator->trans('flash.oops'), FlashType::ERROR);
        }

        $this->getPresenter()->redirect('this');
    }

    /**
     * Handle password reset from email link.
     *
     * @param string|null $token
     * @return void
     * @throws AbortException
     */
    public function actionResetPassword(?string $token = null): void
    {
        if ($token === null) {
            $this->flashMessage($this->translator->trans('flash.resetLinkInvalid'), FlashType::ERROR);
            $this->redirect(':Homepage:Default');
        }

        $tokenEntity = $this->passwordResetTokenManager->findValidToken($token);

        if (!$tokenEntity) {
            $this->flashMessage($this->translator->trans('flash.resetLinkExpired'), FlashType::ERROR);
            $this->redirect(':Homepage:Default');
        }

        $this->template->userId = $tokenEntity->user_id;
        $this->template->tokenId = $tokenEntity->id;
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

    /**
     * Create reset password from link form.
     *
     * @return ResetPasswordFromLink
     */
    public function createComponentResetPasswordFromLinkForm(): ResetPasswordFromLink
    {
        return $this->resetPasswordFromLinkFormFactory->create(
            $this->template->userId,
            $this->template->tokenId,
        );
    }
}
