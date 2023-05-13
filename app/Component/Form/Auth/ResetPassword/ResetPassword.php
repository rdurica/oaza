<?php

declare(strict_types=1);

namespace App\Component\Form\Auth\ResetPassword;

use App\Component\Component;
use App\Model\Service\Authentication\PasswordService;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Forms\Form as NetteForm;
use Nette\Utils\ArrayHash;

/**
 * ResetPassword form.
 *
 * @package   App\Component\Form\Auth\ResetPassword
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class ResetPassword extends Component
{

    /**
     * Constructor.
     *
     * @param Translator      $translator
     * @param PasswordService $passwordService
     */
    public function __construct(
        private readonly Translator      $translator,
        private readonly PasswordService $passwordService,
    )
    {
    }

    /**
     * Create ResetPassword form.
     *
     * @return Form
     */
    public function createComponentForm(): Form
    {
        $form = new Form();

        $form->addText('email', $this->translator->trans('user.email'))
            ->addRule(NetteForm::EMAIL)
            ->setRequired()
            ->setHtmlAttribute('placeholder', $this->translator->trans('user.email'));
        $form->addSubmit('save', $this->translator->trans('button.resetPassword'));
        $form->onSuccess[] = [$this, 'onSuccess'];

        return $form;
    }


    /**
     * Process ResetPassword form.
     *
     * @param Form      $form
     * @param ArrayHash $values
     * @return void
     * @throws AbortException
     */
    public function onSuccess(Form $form, ArrayHash $values): void
    {
        try {
            $this->passwordService->resetPassword($values->email);
        } catch (\Exception $e) {
            // Ignore - not relevant
        }
        $this->getPresenter()->flashMessage($this->translator->trans('flash.newPasswordSent'), FlashType::INFO);
        $this->getPresenter()->redirect('this');
    }
}
