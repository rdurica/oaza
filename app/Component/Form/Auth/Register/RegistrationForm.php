<?php

declare(strict_types=1);

namespace App\Component\Form\Auth\Register;

use App\Component\Component;
use App\Model\Service\Authentication\Authenticator;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Database\UniqueConstraintViolationException;
use Nette\Forms\Form as NetteForm;
use Nette\Utils\ArrayHash;

class RegistrationForm extends Component
{
    public function __construct(
        private readonly Translator $translator,
        private readonly Authenticator $authenticator
    ) {
    }

    protected function createComponentForm(): Form
    {
        $form = new Form();
        $form->addText('email', $this->translator->trans('user.email'))
            ->setHtmlAttribute('placeholder', $this->translator->trans('user.email'))
            ->addRule(NetteForm::EMAIL)
            ->setRequired();
        $form->addText('name', $this->translator->trans('user.nameSurname'))
            ->setHtmlAttribute('placeholder', $this->translator->trans('user.nameSurname'))
            ->setMaxLength(30)
            ->setRequired();
        $form->addInteger('telephone', $this->translator->trans('user.telephone'))
            ->setHtmlAttribute('placeholder', $this->translator->trans('user.telephone'))
            ->setRequired()
            ->setMaxLength(13);
        $form->addPassword('password', $this->translator->trans('user.password'))
            ->setHtmlAttribute('placeholder', $this->translator->trans('user.password'))
            ->setRequired();
        $form->addPassword('passwordVerify', $this->translator->trans('user.passwordVerify'))
            ->setHtmlAttribute('placeholder', $this->translator->trans('user.passwordVerify'))
            ->addRule(NetteForm::EQUAL, 'flash.passwordsDoNotMatch', $form['password'])
            ->setOmitted()
            ->setRequired();
        $form->addCheckbox('licence', '')->setRequired();
        $form->addSubmit('save', $this->translator->trans('button.register'));

        $form->onSuccess[] = [$this, 'onFormSuccess'];

        return $form;
    }

    /**
     * @throws AbortException
     */
    #[NoReturn] public function onFormSuccess(Form $form, ArrayHash $values): void
    {
        try {
            $this->authenticator->createAccount($values->email, $values->password, $values->name, $values->telephone);
            $this->presenter->flashMessage(
                $this->translator->trans('flash.registrationSuccessful'),
                FlashType::SUCCESS
            );
        } catch (UniqueConstraintViolationException $e) {
            $this->presenter->flashMessage(
                $this->translator->trans('flash.emailAlreadyUsedException'),
                FlashType::WARNING
            );
        } catch (\Exception $e) {
            $this->presenter->flashMessage(
                $this->translator->trans('flash.oops'),
                FlashType::ERROR
            );
        }

        $this->presenter->redirect('Homepage:');
    }
}
