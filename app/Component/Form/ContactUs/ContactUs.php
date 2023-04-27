<?php

declare(strict_types=1);

namespace App\Component\Form\ContactUs;

use App\Component\Component;
use App\Model\Service\Mail\MailService;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Nette\Application\UI\Form;
use Nette\Forms\Form as NetteForm;
use Nette\Security\User;

class ContactUs extends Component
{
    public function __construct(
        private readonly Translator $translator,
        private readonly User $user,
        private readonly MailService $mailService
    ) {
    }


    /**
     * Contact Form
     */
    public function createComponentNewEmail(): Form
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
        $form->addSubmit('sent', $this->translator->trans('buttons.send'))
            ->setHtmlAttribute('class', 'btn btn-info');
        $form->onSuccess[] = array($this, 'formSucceed');

        return $form;
    }


    /**
     * Form Confirmation
     */
    public function formSucceed(Form $form, $values)
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
