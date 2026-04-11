<?php declare(strict_types=1);

namespace App\Component\Form\Auth\ResetPasswordFromLink;

use App\Component\Component;
use App\Model\Manager\PasswordResetTokenManager;
use App\Model\Service\Authentication\Authenticator;
use App\Model\Service\Mail\MailService;
use App\Util\FlashType;
use App\Util\OazaConfig;
use Contributte\Translation\Translator;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Forms\Form as NetteForm;
use Nette\Utils\ArrayHash;

/**
 * Reset password from link form.
 *
 * @copyright Copyright (c) 2026, Robert Durica
 * @since     2026-04-03
 */
class ResetPasswordFromLink extends Component
{
    use OazaConfig;

    /**
     * Constructor.
     *
     * @param Translator                 $translator
     * @param Authenticator              $authenticator
     * @param MailService                $mailService
     * @param PasswordResetTokenManager  $passwordResetTokenManager
     * @param int                        $userId
     * @param int                        $tokenId
     */
    public function __construct(
        private readonly Translator $translator,
        private readonly Authenticator $authenticator,
        private readonly MailService $mailService,
        private readonly PasswordResetTokenManager $passwordResetTokenManager,
        private readonly int $userId,
        private readonly int $tokenId,
    ) {
    }

    /**
     * Create form.
     *
     * @return Form
     * @throws Exception
     */
    protected function createComponentForm(): Form
    {
        $form = new Form();
        $form->addPassword('password', $this->translator->trans('user.newPassword'))
            ->addRule(NetteForm::MIN_LENGTH, $this->translator->trans('flash.minPasswordLength'), $this->getConfig('passwordLength'))
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
        try {
            $tokenRow = $this->passwordResetTokenManager->findValidTokenById($this->tokenId);
            if (!$tokenRow || (int) $tokenRow->user_id !== $this->userId) {
                $this->presenter->flashMessage($this->translator->trans('flash.resetLinkExpired'), FlashType::ERROR);
                $this->getPresenter()->redirect(':Homepage:Default');
            }

            $email = $tokenRow->user->email ?? null;

            $this->authenticator->changePassword($this->userId, $values->password);
            $this->passwordResetTokenManager->deleteToken($this->tokenId);

            if ($email) {
                $this->mailService->sendPasswordChangedNotification($email);
            }

            $this->presenter->flashMessage($this->translator->trans('flash.passwordChanged'), FlashType::SUCCESS);
        } catch (Exception) {
            $this->presenter->flashMessage($this->translator->trans('flash.oops'), FlashType::ERROR);
        }

        $this->getPresenter()->redirect(':Homepage:Default');
    }
}
