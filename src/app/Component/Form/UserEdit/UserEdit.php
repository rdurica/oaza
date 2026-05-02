<?php declare(strict_types=1);

namespace App\Component\Form\UserEdit;

use App\Component\Component;
use App\Model\Manager\UserManager;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Exception;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * User edit form.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
class UserEdit extends Component
{
    /**
     * Constructor.
     *
     * @param int|null    $id
     * @param Translator  $translator
     * @param UserManager $userManager
     */
    public function __construct(
        public ?int $id,
        private readonly Translator $translator,
        private readonly UserManager $userManager,
    )
    {
    }

    /**
     * Create form.
     *
     * @return Form
     */
    public function createComponentForm(): Form
    {
        $form = new Form();
        $form->addHidden('id');
        $form->addText('name', $this->translator->trans('user.nameSurname'))
            ->setRequired()
            ->setMaxLength(45)
            ->setHtmlAttribute('class', 'form-control');
        $form->addEmail('email', $this->translator->trans('user.email'))
            ->setRequired()
            ->setHtmlAttribute('class', 'form-control')
            ->addRule(Form::EMAIL);
        $form->addText('telephone', $this->translator->trans('user.telephone'))
            ->setMaxLength(20)
            ->setHtmlAttribute('class', 'form-control');
        $form->addSelect('role', 'Role', [
            'user' => $this->translator->trans('roleUser'),
            'admin' => $this->translator->trans('roleAdmin'),
        ])->setHtmlAttribute('class', 'form-control');
        $form->addSelect('enabled', 'Status', [
            1 => $this->translator->trans('userStatusEnabled'),
            0 => $this->translator->trans('userStatusDisabled'),
        ])->setHtmlAttribute('class', 'form-control');
        $form->addSubmit('confirm', $this->translator->trans('button.save'))
            ->setHtmlAttribute('class', 'btn btn-info admin-form-submit');
        $form->onSuccess[] = [$this, 'onSuccess'];

        if ($this->id)
        {
            $data = $this->userManager->getById($this->id);
            if ($data)
            {
                $form->setDefaults($data);
            }
        }

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
    public function onSuccess(Form $form, ArrayHash $values): void
    {
        $userId = (int) $values->id;
        $currentUserId = $this->getPresenter()->getUser()->getId();

        if ($userId === $currentUserId && $values->role !== 'admin')
        {
            $this->getPresenter()->flashMessage(
                $this->translator->trans('flash.cannotChangeOwnRole'),
                FlashType::ERROR,
            );
            $this->getPresenter()->redirect('this');
        }

        $existing = $this->userManager->findByEmail($values->email)->where('id != ?', $userId)->fetch();
        if ($existing)
        {
            $this->getPresenter()->flashMessage(
                $this->translator->trans('flash.emailAlreadyUsedException'),
                FlashType::WARNING,
            );
            $this->getPresenter()->redirect('this');
        }

        try
        {
            $this->userManager->updateUser($userId, [
                'name' => $values->name,
                'email' => $values->email,
                'telephone' => $values->telephone,
                'role' => $values->role,
                'enabled' => (int) $values->enabled,
            ]);
            $this->getPresenter()->flashMessage(
                $this->translator->trans('flash.userUpdated'),
                FlashType::SUCCESS,
            );
        }
        catch (Exception)
        {
            $this->getPresenter()->flashMessage($this->translator->trans('flash.oops'), FlashType::ERROR);
        }

        $this->getPresenter()->redirect('Users:');
    }
}
