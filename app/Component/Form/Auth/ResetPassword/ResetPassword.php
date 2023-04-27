<?php

declare(strict_types=1);

namespace App\Component\Form\Auth\ResetPassword;

use App\Component\Component;
use App\Model\Service\Authentication\PasswordService;
use Contributte\Translation\Translator;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Forms\Form as NetteForm;
use Nette\Utils\ArrayHash;

class ResetPassword extends Component
{
    public function __construct(
        private readonly Translator $translator,
        private readonly PasswordService $passwordService,
    ) {
    }

    public function createComponentForm(): Form
    {
        $form = new Form();

        $form->addText('email', $this->translator->trans('user.email'))
            ->addRule(NetteForm::EMAIL)
            ->setRequired()
            ->setHtmlAttribute('placeholder', $this->translator->trans('user.email'));
        $form->addSubmit('save', $this->translator->trans('button.resetPassword'));
        $form->onSuccess[] = [$this, 'success'];

        return $form;
    }


    /**
     * @throws AbortException
     */
    #[NoReturn] public function success(Form $form, ArrayHash $values): void
    {

        $this->passwordService->resetPassword($values->email);
        $this->getPresenter()->redirect('this');
    }

}
