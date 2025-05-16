<?php

declare(strict_types=1);

namespace App\Component\Form\Restriction;

use App\Component\Component;
use App\Model\Manager\NewsManager;
use App\Model\Manager\ReservationManager;
use App\Model\Manager\RestrictionManager;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;

/**
 * Restriction form.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
class Restriction extends Component
{
    /**
     * Constructor.
     *
     * @param RestrictionManager $restrictionManager
     * @param NewsManager        $newsModel
     * @param ReservationManager $reservationManager
     * @param Translator         $translator
     */
    public function __construct(
        private readonly RestrictionManager $restrictionManager,
        private readonly NewsManager $newsModel,
        private readonly ReservationManager $reservationManager,
        private readonly Translator $translator
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

        $form->addText('from', 'Od')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired();
        $form->addText('to', 'Do')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired();
        $form->addTextArea('message', 'Zpráva')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('id', 'textarea');
        $form->addSelect('showNewsOnHomepage', $this->translator->trans('forms.showNewsHomepage'), [0 => 'Ne', 1 => 'Ano'])
            ->setHtmlAttribute('class', 'form-control')
            ->setDefaultValue(0);
        $form->addSubmit('save', 'Uložit')
            ->setHtmlAttribute('style', 'float: right;')
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
     * @throws Exception
     */
    #[NoReturn]
    public function onSuccess(Form $form, ArrayHash $values): void
    {
        $createNews = $values->showNewsOnHomepage;
        $from = new DateTime($values->from);
        $to = new DateTime($values->to);

        //Todo: Fix logic
        // 1. create restriction
        // 2. create new if requested
        // 3. delete reservations & send mails
        try
        {
            $this->restrictionManager->create($from, $to, $values->message);

            if ($createNews === true)
            {
                $this->newsModel->save(null, 'Omezení provozu', $values->showNewsOnHomepage, $values->message, false);
            }

            $this->reservationManager->blockDays($from, $to);

            $this->presenter->flashMessage('Omezení provozu přidáno', FlashType::SUCCESS);
        }
        catch (Exception)
        {
            $this->getPresenter()->flashMessage($this->translator->trans('flash.oops'), FlashType::ERROR);
        }

        $this->presenter->redirect('Restrictions:');
    }
}
