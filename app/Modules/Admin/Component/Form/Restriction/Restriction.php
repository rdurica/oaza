<?php

declare(strict_types=1);

namespace App\Modules\Admin\Component\Form\Restriction;

use App\Component\Component;
use App\Model\Manager\ReservationManager;
use App\Modules\Admin\Manager\NewsManager;
use App\Modules\Admin\Manager\RestrictionManager;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;

/**
 * Restriction form.
 *
 * @package   App\Modules\Admin\Component\Form\Restriction
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
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
        private readonly NewsManager        $newsModel,
        private readonly ReservationManager $reservationManager,
        public readonly Translator          $translator
    )
    {
    }

    /**
     * Create Restriction form
     *
     * @return Form
     */
    public function createComponentForm(): Form
    {
        $form = new Form();

        $form->addText('from', 'Od')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired(true);
        $form->addText('to', 'Do')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired(true);
        $form->addTextArea('message', 'Zpráva')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('id', 'textarea');
        $form->addSelect(
            'showNewsOnHomepage',
            $this->translator->trans('forms.showNewsHomepage'),
            [0 => 'Ne', 1 => 'Ano']
        )
            ->setHtmlAttribute('class', 'form-control')
            ->setDefaultValue(0);
        $form->addSubmit('save', 'Uložit')
            ->setHtmlAttribute('style', 'float: right;')
            ->setHtmlAttribute('class', 'btn btn-info');

        $form->onSuccess[] = [$this, 'onSuccess'];

        return $form;
    }


    /**
     * Process Restriction form.
     *
     * @param Form      $form
     * @param ArrayHash $values
     * @return void
     * @throws AbortException
     */
    public function onSuccess(Form $form, ArrayHash $values): void
    {
        $createNews = $values->showNewsOnHomepage;
        $from = new DateTime($values->from);
        $to = new DateTime($values->to);

        //Todo: Fix logic
        // 1. create restriction
        // 2. create new if requested
        // 3. delete reservations & send mails
        try {
            $this->restrictionManager->create($from, $to, $values->message);

            if ($createNews === true) {
                $this->newsModel->save(null, "Omezení provozu", $values->showNewsOnHomepage, $values->message, false);
            }

            $this->reservationManager->blockDays($from, $to);

            $this->presenter->flashMessage('Omezení provozu přidáno', FlashType::SUCCESS);
        } catch (\Exception $exception) {
            $this->presenter->flashMessage($exception->getMessage(), FlashType::ERROR);
        }

        $this->presenter->redirect('Restrictions:');
    }
}
