<?php declare(strict_types=1);

namespace App\Component\Form\Auth\Register;

use App\Component\Component;
use App\Exception\OazaException;
use App\Model\Service\Authentication\Authenticator;
use App\Model\Service\Mail\MailService;
use App\Util\FlashType;
use App\Util\OazaConfig;
use Contributte\Translation\Translator;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Database\UniqueConstraintViolationException;
use Nette\Forms\Form as NetteForm;
use Nette\Utils\ArrayHash;

/**
 * Registration form.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
class Registration extends Component
{
    use OazaConfig;

    /**
     * Constructor.
     *
     * @param Translator    $translator
     * @param Authenticator $authenticator
     * @param MailService   $mailService
     */
    public function __construct(
        private readonly Translator $translator,
        private readonly Authenticator $authenticator,
        private readonly MailService $mailService,
    )
    {
    }

    /**
     * Form.
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
            ->addRule($form::Pattern, $this->translator->trans('flash.telephoneFormat'), $this->getConfig('telephoneRegex'))
            ->setMaxLength(9);
        $form->addPassword('password', $this->translator->trans('user.password'))
            ->setHtmlAttribute('placeholder', $this->translator->trans('user.password'))
            ->addRule(NetteForm::MIN_LENGTH, $this->translator->trans('flash.minPasswordLength'), $this->getConfig('passwordLength'))
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
        try
        {
            $this->authenticator->createAccount($values->email, $values->password, $values->name, (int)$values->telephone);
            $this->mailService->sendUserRegisteredNotification($values->email);
            $this->presenter->flashMessage($this->translator->trans('flash.registrationSuccessful'), FlashType::SUCCESS);
        }
        catch (UniqueConstraintViolationException)
        {
            $this->presenter->flashMessage($this->translator->trans('flash.emailAlreadyUsedException'), FlashType::WARNING);
        }
        catch (Exception)
        {
            $this->presenter->flashMessage($this->translator->trans('flash.oops'), FlashType::ERROR);
        }

        $this->presenter->redirect('Homepage:');
    }
}
