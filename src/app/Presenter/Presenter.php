<?php declare(strict_types=1);

namespace App\Presenter;

use App\Component\Form\Auth\Login\Login;
use App\Component\Form\Auth\Login\LoginFormFactory;
use App\Component\Form\Auth\Register\Registration;
use App\Component\Form\Auth\Register\RegistrationFormFactory;
use App\Component\Form\Auth\ResetPassword\ResetPassword;
use App\Component\Form\Auth\ResetPassword\ResetPasswordFormFactory;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Nette\Application\AbortException;
use Nette\Application\UI\Presenter as NettePresenter;
use Nette\DI\Attributes\Inject;

/**
 * Base presenter for public pages.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
abstract class Presenter extends NettePresenter
{
    #[Inject]
    public LoginFormFactory $loginForm;

    #[Inject]
    public RegistrationFormFactory $registrationForm;

    #[Inject]
    public ResetPasswordFormFactory $resetPasswordForm;

    #[Inject]
    public Translator $translator;

    /**
     * Checks if the user logged-in and password needs to be changed.
     *
     * @return void
     * @throws AbortException
     */
    public function startup(): void
    {
        parent::startup();
        if (
            $this->getUser()->identity?->data['needNewPassword'] === true &&
            $this->getPresenter()->view !== 'changePassword'
        )
        {
            $this->redirect(':User:ChangePassword');
        }
    }

    /**
     * Create login form.
     *
     * @return Login
     */
    protected function createComponentLoginForm(): Login
    {
        return $this->loginForm->create();
    }

    /**
     * Create registration form.
     *
     * @return Registration
     */
    protected function createComponentRegistrationForm(): Registration
    {
        return $this->registrationForm->create();
    }

    /**
     * Create reset password form.
     *
     * @return ResetPassword
     */
    public function createComponentResetPasswordForm(): ResetPassword
    {
        return $this->resetPasswordForm->create();
    }

    /**
     * Log-out user and clear identity.
     *
     * @return void
     * @throws AbortException
     */
    public function handleOut(): void
    {
        $this->getUser()->logout(true);
        $this->presenter->flashMessage($this->translator->trans('flash.loggedOut'), FlashType::INFO);
        $this->presenter->redirect(':Homepage:Default');
    }
}
