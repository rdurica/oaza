<?php

declare(strict_types=1);

namespace App\Component\Form\Auth\Login;

use App\Component\Component;
use App\Exception\UserBlockedException;
use App\Model\Service\Authentication\Authenticator;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Forms\Form as NetteForm;
use Nette\Security\AuthenticationException;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

/**
 * Login form.
 *
 * @package   App\Component\Form\Auth\Login
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class Login extends Component
{
    /**
     * Constructor.
     *
     * @param Translator    $translator
     * @param User          $user
     * @param Authenticator $authenticator
     */
    public function __construct(
        private readonly Translator $translator,
        private readonly User $user,
        private readonly Authenticator $authenticator
    ) {
    }


    /**
     * Create Login form.
     *
     * @return Form
     */
    public function createComponentLoginForm(): Form
    {
        $form = new Form();
        $form->addText("email", $this->translator->trans("user.email"))
            ->setHtmlAttribute("placeholder", $this->translator->trans("user.email"))
            ->addRule(NetteForm::EMAIL)
            ->setRequired();
        $form->addPassword("password", $this->translator->trans("user.password"))
            ->setHtmlAttribute("placeholder", $this->translator->trans("user.password"))
            ->setRequired();
        $form->addSubmit("send", $this->translator->trans("button.login"))
            ->setHtmlAttribute("class", "btn btn-info");

        $form->onSuccess[] = [$this, "onSuccess"];

        return $form;
    }


    /**
     * Process Login form.
     *
     * @param Form      $form
     * @param ArrayHash $values
     * @return void
     * @throws AbortException
     */
    public function onSuccess(Form $form, ArrayHash $values): void
    {
        try {
            $identity = $this->authenticator->authenticate($values->email, $values->password);
            $this->user->setExpiration("14 days", false);
            $this->user->login($identity);

            ($identity->needNewPassword === true) ? $this->changePasswordRedirect() : $this->loggedInRedirect();
        } catch (AuthenticationException $e) {
            $this->getPresenter()->flashMessage(
                $this->translator->trans("flash.authenticationException"),
                FlashType::WARNING
            );
        } catch (UserBlockedException $e) {
            $this->getPresenter()->flashMessage(
                $this->translator->trans("flash.blockedUserException"),
                FlashType::ERROR
            );
        }

        $this->getPresenter()->redirect("this");
    }


    /**
     * @throws AbortException
     */
    #[NoReturn] private function changePasswordRedirect(): void
    {
        $this->getPresenter()->flashMessage(
            $this->translator->trans("flash.newPasswordRequired"),
            FlashType::SUCCESS
        );
        $this->getPresenter()->redirect(":User:changePassword");
    }


    /**
     * @throws AbortException
     */
    #[NoReturn] private function loggedInRedirect(): void
    {
        $this->getPresenter()->flashMessage(
            $this->translator->trans("flash.loggedIn"),
            FlashType::SUCCESS
        );
        $this->getPresenter()->redirect("this");
    }
}
