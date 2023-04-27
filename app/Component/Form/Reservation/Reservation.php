<?php

declare(strict_types=1);

namespace App\Component\Form\Reservation;

use App\Component\Component;
use App\Component\ComponentRenderer;
use App\Model\Service\ReservationService;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Nette\Application\UI\Form;
use Nette\Forms\Form as NetteForm;
use Nette\Security\User;

class Reservation extends Component
{
    use ComponentRenderer;

    public function __construct(
        private readonly Translator $translator,
        private readonly User $user,
        private readonly ReservationService $reservationService,
    ) {
    }


    public function createComponentReservationForm(): Form
    {
        $form = new Form();
        $form->addHidden('rezervationDate');
        if (!$this->user->isLoggedIn()) {
            $form->addText('email', $this->translator->trans('user.email'))
                ->setHtmlAttribute('placeholder', $this->translator->trans('user.email'))
                ->setHtmlAttribute('class', 'form-control')
                ->addRule(NetteForm::EMAIL);
            $form->addText('name', $this->translator->trans('user.nameSurname'))
                ->setHtmlAttribute('placeholder', $this->translator->trans('user.nameSurname'))
                ->setHtmlAttribute('class', 'form-control')
                ->setRequired();
            $form->addText('telefon', $this->translator->trans('user.telephone'))
                ->setHtmlAttribute('placeholder', $this->translator->trans('user.telephone'))
                ->setHtmlAttribute('class', 'form-control')
                ->setRequired();
        }
        $form->addSelect('count', $this->translator->trans('forms.qty'))
            ->setHtmlAttribute('class', 'form-control')
            ->setItems([1, 2, 3, 4, 5,], false)
            ->setRequired();
        $form->addSelect('child', $this->translator->trans('forms.childs'))
            ->setHtmlAttribute('class', 'form-control')
            ->setItems(["Ne", "Ano",], true)
            ->setRequired();
        $form->addTextArea('comment', $this->translator->trans('forms.comment'))
            ->setHtmlAttribute("rows", 5)
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('placeholder', $this->translator->trans('forms.comment'));
        $form->addSubmit('send', $this->translator->trans('buttons.reserve'))
            ->setHtmlAttribute('class', 'btn btn-info');

        $form->onSuccess[] = [$this, 'formSucceeded'];

        return $form;
    }


    public function formSucceeded(Form $form, $values)
    {
        try {
            if ($this->user->isLoggedIn()) {
                $values->user_id = $this->user->id;
            }
            $this->reservationService->insert($values);
            $this->presenter->flashMessage($this->translator->trans('flash.reservationCreated'), FlashType::SUCCESS);
        } catch (\Exception $ex) {
            $this->presenter->flashMessage($ex->getMessage(), FlashType::ERROR);
        }
        $this->getPresenter()->redirect('this');
    }
}
