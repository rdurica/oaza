<?php

declare(strict_types=1);

namespace App\Presenter;

use App\Component\Form\Auth\Login\ILoginForm;
use App\Component\Form\Auth\Login\LoginForm;
use App\Component\Form\Auth\Register\IRegistrationForm;
use App\Component\Form\Auth\Register\RegistrationForm;
use App\Component\Form\Auth\ResetPassword\IResetPassword;
use App\Component\Form\Auth\ResetPassword\ResetPassword;
use App\Util\FlashType;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\AbortException;
use Nette\Application\UI\Presenter as NettePresenter;
use Nette\DI\Attributes\Inject;

abstract class Presenter extends NettePresenter
{
    #[Inject]
    public ILoginForm $loginForm;

    #[Inject]
    public IRegistrationForm $registration;
    #[Inject]
    public IResetPassword $resetPassword;

    public function startup(): void
    {
        parent::startup();
        if (
            $this->user->identity?->data["needNewPassword"] === true &&
            $this->getPresenter()->view !== "changePassword"
        ) {
            $this->redirect(':User:ChangePassword');
        }
    }

    /**
     * Form
     */
    protected function createComponentLogin(): LoginForm
    {
        return $this->loginForm->create();
    }


    /**
     * Form
     */
    protected function createComponentRegistration(): RegistrationForm
    {
        return $this->registration->create();
    }


    /**
     * Logout user
     * @throws AbortException
     */
    #[NoReturn] public function handleOut(): void
    {
        $this->getUser()->logout(true);
        $this->presenter->flashMessage("Odhlášení proběhlo úspěšně", FlashType::INFO);
        $this->presenter->redirect('Homepage:Default');
    }


    /**
     * Form
     */
    public function createComponentResetPassword(): ResetPassword
    {
        return $this->resetPassword->create();
    }
}
