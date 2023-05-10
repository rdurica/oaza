<?php

declare(strict_types=1);

namespace App\Modules\Admin\Component\Form\Restriction;

use App\Component\Component;
use App\Model\Manager\NewsManager;
use App\Model\Manager\ReservationManager;
use App\Model\Manager\RestrictionManager;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;

class Restriction extends Component
{
    public function __construct(
        private readonly RestrictionManager $restrictionManager,
        private readonly NewsManager $newsModel,
        private readonly ReservationManager $reservationManager,
        public readonly Translator $translator
    ) {
    }

    /**
     * Restriction Form
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

        $form->onSuccess[] = [$this, 'success'];

        return $form;
    }


    /**
     * @throws AbortException
     * @throws \Exception
     */
    public function success(Form $form, ArrayHash $values)
    {
        $createNews = $values->showNewsOnHomepage;
        $from = new DateTime($values->from);
        $to = new DateTime($values->to);

        //Todo: Fix logic
        try {
            $this->restrictionManager->create($from, $to, $values->message);

            if ($createNews === true) {
                $this->newsModel->save(null, "Omezení provozu", $values->showNewsOnHomepage, $values->message, false);
            }

            $this->reservationManager->insertRestricted($from, $to);

            $this->presenter->flashMessage('Omezení provozu přidáno', FlashType::SUCCESS);
        } catch (\Exception $exception) {
            $this->presenter->flashMessage($exception->getMessage(), FlashType::ERROR);
        }

        $this->presenter->redirect('Restrictions:');
    }
}
