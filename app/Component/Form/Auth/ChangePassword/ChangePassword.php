<?php

declare(strict_types=1);

namespace App\Component\Form\Auth\ChangePassword;

use App\Component\Component;
use App\Exception\OazaException;
use App\Model\Service\Authentication\Authenticator;
use App\Model\Service\Mail\MailService;
use App\Util\FlashType;
use App\Util\OazaConfig;
use Contributte\Translation\Translator;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Forms\Form as NetteForm;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

/**
 * ChangePassword form.
 *
 * @package   App\Component\Form\Auth\ChangePassword
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class ChangePassword extends Component
{
    use OazaConfig;

    /**
     * Constructor.
     *
     * @param Translator    $translator
     * @param Authenticator $authenticator
     * @param User          $user
     * @param MailService   $mailService
     */
    public function __construct(
        private readonly Translator    $translator,
        private readonly Authenticator $authenticator,
        private readonly User          $user,
        private readonly MailService   $mailService
    )
    {
    }


    /**
     * Create ChangePassword form.
     *
     * @return Form
     * @throws OazaException
     */
    protected function createComponentForm(): Form
    {
        $form = new Form();
        $form->addPassword('password', $this->translator->trans('user.newPassword'))
            ->addRule(
                NetteForm::MIN_LENGTH,
                $this->translator->trans('flash.minPasswordLength'),
                $this->getConfig("passwordLength")
            )
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
     * Process ChangePassword form.
     *
     * @param Form      $form
     * @param ArrayHash $values
     * @return void
     * @throws AbortException
     */
    public function onSuccess(Form $form, ArrayHash $values): void
    {
        try {
            $this->authenticator->changePassword($this->user->id, $values->password);
            $this->mailService->passwordChanged($this->user->identity->email);
            $this->presenter->flashMessage($this->translator->trans('flash.passwordChanged'), FlashType::SUCCESS);
        } catch (\Exception $e) {
            $this->presenter->flashMessage($this->translator->trans("flash.oops"), FlashType::ERROR);
        }
        $this->user->logout(true);
        $this->getPresenter()->redirect('Homepage:');
    }
}
