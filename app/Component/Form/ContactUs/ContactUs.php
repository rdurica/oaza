<?php

declare(strict_types=1);

namespace App\Component\Form\ContactUs;

use App\Component\Component;
use App\Model\Service\Mail\MailService;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Forms\Form as NetteForm;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

/**
 * ContactUs form.
 *
 * @package   App\Component\Form\ContactUs
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
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
        private readonly MailService $mailService
    ) {
    }


    /**
     * Create ContactUs form.
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
            ->setHtmlAttribute('style', 'height: 30vh;');
        $form->addReCaptcha('recaptcha', $label = 'Captcha', $required = true, $message = 'Are you a bot?');
        $form->addSubmit('sent', $this->translator->trans('button.send'))
            ->setHtmlAttribute('class', 'btn btn-info');
        $form->onSuccess[] = [$this, 'onSuccess'];

        return $form;
    }


    /**
     * Process ContactUs form.
     *
     * @param Form      $form
     * @param ArrayHash $values
     * @return void
     * @throws AbortException
     */
    public function onSuccess(Form $form, ArrayHash $values): void
    {
        try {
            $this->mailService->contactUs($values->from, $values->message);
            $this->presenter->flashMessage($this->translator->trans('flash.contactUsSuccess'), FlashType::INFO);
        } catch (\Exception $ex) {
            $this->presenter->flashMessage($ex->getMessage(), FlashType::ERROR);
        }

        $this->presenter->redirect("this");
    }
}
