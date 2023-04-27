<?php

declare(strict_types=1);

namespace App\Component\Form\Restriction;

use App\Component\Component;
use App\Model\Manager\NewsManager;
use App\Model\Manager\ReservationManager;
use App\Model\Manager\RestrictionManager;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;

class RestrictionForm extends Component
{
    public function __construct(
        private readonly RestrictionManager $restrictionModel,
        private readonly NewsManager $newsModel,
        private readonly ReservationManager $reservationManager
    ) {
    }

    /**
     * Restriction Form
     * @return Form
     */
    public function createComponentForm()
    {
        $form = new Form();

        $form->addText('from', 'Od')
            ->setAttribute('class', 'form-control')
            ->setRequired(true);
        $form->addText('to', 'Do')
            ->setAttribute('class', 'form-control')
            ->setRequired(true);
        $form->addTextArea('message', 'Zpráva')
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'textarea');
        $form->addCheckbox('create_news', ' Vytvořit novinku na hlavní stránku ?')
            ->setDefaultValue(0);
        $form->addSubmit('save', 'Uložit')
            ->setAttribute('class', 'btn btn-success');

        $form->onSuccess[] = [$this, 'success'];

        return $form;
    }


    public function success(Form $form, ArrayHash $vals)
    {
        $createNews = $vals->create_news;
        unset($vals->create_news);

        $vals->from = new DateTime($vals->from);
        $vals->to = new DateTime($vals->to);


        try {
            $this->restrictionModel->getEntitiyTable()->insert($vals);

            if ($createNews === true) {
                $this->insertNews($vals->message);
            }

            $this->reservationManager->insertRestricted($vals->from, $vals->to);

            $this->presenter->flashMessage('Omezení provozu přidáno', 'success');
        } catch (\Exception $exception) {
            $this->presenter->flashMessage($exception->getMessage(), 'danger');
        }

        $this->presenter->redirect('Manage:');
    }


    private function insertNews($message)
    {
        $news = new ArrayHash();
        $news->name = 'Omezení provozu';
        $news->text = $message;
        $news->show = 0;
        $news->show_homepage = 1;

        $this->newsModel->getEntityTable()->insert($news);
    }
}
