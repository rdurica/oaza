<?php

declare(strict_types=1);

namespace App\Component\Form\Auth\ChangePassword;

use App\Component\Component;
use App\Model\Service\Authentication\Authenticator;
use App\Model\Service\Mail\MailService;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Forms\Form as NetteForm;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

class ChangePassword extends Component
{
    public function __construct(
        private readonly Translator $translator,
        private readonly Authenticator $authenticator,
        private readonly User $user,
        private readonly MailService $mailService
    ) {
    }


    /**
     * Register Form
     */
    protected function createComponentForm(): Form
    {
        $form = new Form();
        $form->addPassword('password', $this->translator->trans('user.newPassword'))
            ->setRequired()
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('placeholder', $this->translator->trans('user.newPassword'));
        $form->addPassword('passwordVerify', $this->translator->trans('user.passwordVerify'))
            ->addRule(NetteForm::EQUAL, $this->translator->trans('flash.equalPassword'), $form['password'])
            ->setOmitted()
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('placeholder', $this->translator->trans('user.passwordVerify'));
        $form->addSubmit('save', $this->translator->trans('button.changePassword'))
            ->setHtmlAttribute('class', 'btn btn-info');

        $form->onSuccess[] = [$this, 'onSuccess'];

        return $form;
    }


    /**
     * @throws AbortException
     */
    public function onSuccess(Form $form, ArrayHash $values): void
    {
        try {
            $this->authenticator->changePassword($this->user->id, $values->password);
            $this->mailService->passwordChanged($this->user->identity->email);
            $this->user->logout(true);
            $this->presenter->flashMessage($this->translator->trans('flash.passwordChanged'), FlashType::SUCCESS);
        } catch (\Exception $e) {
            $this->presenter->flashMessage($this->translator->trans("flash.oops"), FlashType::ERROR);
        }
        $this->getPresenter()->redirect('Homepage:');
    }
}
