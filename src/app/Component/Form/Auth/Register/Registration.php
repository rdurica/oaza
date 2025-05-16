<?php

declare(strict_types=1);

namespace App\Component\Form\Auth\Register;

use App\Component\Component;
use App\Exception\OazaException;
use App\Model\Service\Authentication\Authenticator;
use App\Util\FlashType;
use App\Util\OazaConfig;
use Contributte\Translation\Translator;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Database\UniqueConstraintViolationException;
use Nette\Forms\Form as NetteForm;
use Nette\Utils\ArrayHash;

/**
 * Registration form.
 *
 * @package   App\Component\Form\Auth\Register
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class Registration extends Component
{
    use OazaConfig;

    /**
     * Constructor.
     *
     * @param Translator    $translator
     * @param Authenticator $authenticator
     */
    public function __construct(
        private readonly Translator $translator,
        private readonly Authenticator $authenticator
    ) {
    }

    /**
     * Create Registration form.
     *
     * @return Form
     * @throws OazaException
     */
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
        $form->addText('telephone', $this->translator->trans('user.telephone'))
            ->setHtmlAttribute('placeholder', $this->translator->trans('user.telephone'))
            ->setRequired()
            ->addRule(
                $form::Pattern,
                $this->translator->trans('flash.telephoneFormat'),
                $this->getConfig("telephoneRegex")
            )
            ->setMaxLength(9);
        $form->addPassword('password', $this->translator->trans('user.password'))
            ->setHtmlAttribute('placeholder', $this->translator->trans('user.password'))
            ->addRule(
                NetteForm::MIN_LENGTH,
                $this->translator->trans('flash.minPasswordLength'),
                $this->getConfig("passwordLength")
            )
            ->setRequired();
        $form->addPassword('passwordVerify', $this->translator->trans('user.passwordVerify'))
            ->setHtmlAttribute('placeholder', $this->translator->trans('user.passwordVerify'))
            ->addRule(NetteForm::EQUAL, $this->translator->trans('flash.passwordsDoNotMatch'), $form['password'])
            ->setOmitted()
            ->setRequired();
        $form->addCheckbox('licence', '')->setRequired();
        $form->addSubmit('save', $this->translator->trans('button.register'));

        $form->onSuccess[] = [$this, 'onSuccess'];

        return $form;
    }

    /**
     * Process Registration form.
     *
     * @param Form      $form
     * @param ArrayHash $values
     * @return void
     * @throws AbortException
     */
    public function onSuccess(Form $form, ArrayHash $values): void
    {
        try {
            $this->authenticator->createAccount(
                $values->email,
                $values->password,
                $values->name,
                (int)$values->telephone
            );
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
