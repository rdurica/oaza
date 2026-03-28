<?php

declare(strict_types=1);

namespace App\Component\Form\ContactUs;

use App\Component\Component;
use App\Model\Service\Mail\MailService;
use App\Model\Service\Security\TurnstileVerifier;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Forms\Form as NetteForm;
use Nette\Security\User;
use Nette\Utils\ArrayHash;
use ReflectionException;

/**
 * ContactUs form.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
class ContactUs extends Component
{
    /**
     * Constructor.
     *
     * @param Translator  $translator
     * @param User        $user
     * @param MailService $mailService
     */
    public function __construct(
        private readonly Translator $translator,
        private readonly User $user,
        private readonly MailService $mailService,
        private readonly TurnstileVerifier $turnstileVerifier,
    ) {}

    /**
     * Render component.
     *
     * @return void
     * @throws ReflectionException
     */
    public function render(): void
    {
        $this->template->turnstileSiteKey = $this->turnstileVerifier->getSiteKey();
        parent::render();
    }

    /**
     * Form.
     *
     * @return Form
     */
    public function createComponentForm(): Form
    {
        $form = new Form();
        if (!$this->user->isLoggedIn()) {
            $form->addText('from', $this->translator->trans('user.email'))
                ->setHtmlAttribute('class', 'form-control')
                ->setHtmlAttribute('placeholder', $this->translator->trans('user.email'))
                ->setRequired()
                ->addRule(NetteForm::EMAIL);
        } else {
            $form->addHidden('from', $this->translator->trans('user.email'))
                ->setDefaultValue($this->user->identity->email);
        }
        $form->addTextArea('message', $this->translator->trans('forms.message'))
            ->setRequired()
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('id', 'textarea')
            ->setHtmlAttribute('placeholder', $this->translator->trans('forms.message'))
            ->setHtmlAttribute('style', 'height: 20vh;');
        $form->addSubmit('sent', $this->translator->trans('button.send'))
            ->setHtmlAttribute('class', 'btn btn-info');
        $form->onSuccess[] = [$this, 'onSuccess'];

        return $form;
    }

    /**
     * Process form.
     *
     * @param Form      $form
     * @param ArrayHash $values
     *
     * @return void
     * @throws AbortException
     */
    #[NoReturn]
    public function onSuccess(Form $form, ArrayHash $values): void
    {
        if (!$this->validateTurnstile()) {
            $this->presenter->flashMessage($this->translator->trans('flash.turnstileFailed'), FlashType::ERROR);
            $this->presenter->redirect('this');
        }

        try {
            $this->mailService->sendContactFormMessage($values->from, $values->message);
            $this->presenter->flashMessage($this->translator->trans('flash.contactUsSuccess'), FlashType::INFO);
        } catch (Exception) {
            $this->presenter->flashMessage($this->translator->trans('flash.oops'), FlashType::ERROR);
        }

        $this->presenter->redirect('this');
    }

    /**
     * Validates Turnstile token from submitted form.
     *
     * @return bool
     */
    private function validateTurnstile(): bool
    {
        $httpRequest = $this->getPresenter()->getHttpRequest();
        $token = (string) $httpRequest->getPost('cf-turnstile-response');

        return $this->turnstileVerifier->verify($token, $httpRequest->getRemoteAddress());
    }
}
