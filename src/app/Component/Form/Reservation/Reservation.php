<?php

declare(strict_types=1);

namespace App\Component\Form\Reservation;

use App\Component\Component;
use App\Dto\CreateReservationData;
use App\Exception\CapacityExceededException;
use App\Exception\EmailNotSentException;
use App\Exception\OazaException;
use App\Exception\ReservationInPastException;
use App\Model\Service\ReservationService;
use App\Util\FlashType;
use App\Util\OazaConfig;
use Contributte\Translation\Translator;
use Exception;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Forms\Form as NetteForm;
use Nette\Security\User;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;
use Tracy\Debugger;

/**
 * Reservation form.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
class Reservation extends Component
{
    use OazaConfig;

    /**
     * Constructor.
     *
     * @param Translator         $translator
     * @param User               $user
     * @param ReservationService $reservationService
     */
    public function __construct(
        private readonly Translator $translator,
        private readonly User $user,
        private readonly ReservationService $reservationService,
    ) {
    }

    /**
     * Form.
     *
     * @return Form
     * @throws OazaException
     */
    public function createComponentForm(): Form
    {
        $form = new Form();
        $form->addHidden('date');
        if (!$this->user->isLoggedIn()) {
            $form->addText('email', $this->translator->trans('user.email'))
                ->setHtmlAttribute('placeholder', $this->translator->trans('user.email'))
                ->setHtmlAttribute('class', 'form-control')
                ->addRule(NetteForm::EMAIL);
            $form->addText('name', $this->translator->trans('user.nameSurname'))
                ->setHtmlAttribute('placeholder', $this->translator->trans('user.nameSurname'))
                ->setHtmlAttribute('class', 'form-control')
                ->setRequired();
            $form->addText('telephone', $this->translator->trans('user.telephone'))
                ->setHtmlAttribute('placeholder', $this->translator->trans('user.telephone'))
                ->setRequired()
                ->addRule(
                    $form::Pattern,
                    $this->translator->trans('flash.telephoneFormat'),
                    $this->getConfig('telephoneRegex')
                )
                ->setMaxLength(9);
        }
        $form->addRadioList('count', $this->translator->trans('forms.qty'))
            ->setItems([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'])
            ->setRequired();
        $form->addCheckbox('has_children', $this->translator->trans('forms.childs'));
        $form->addTextArea('comment', $this->translator->trans('forms.comment'))
            ->setHtmlAttribute('rows', 5)
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('placeholder', $this->translator->trans('forms.comment'));
        $form->addSubmit('send', $this->translator->trans('button.reserve'))
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
    public function onSuccess(Form $form, ArrayHash $values): void
    {
        try {
            if ($this->user->isLoggedIn()) {
                $values->user_id = $this->user->getId();
            }

            $this->reservationService->create($this->mapReservationData($values));
            $this->getPresenter()->flashMessage(
                $this->translator->trans('flash.reservationCreated'),
                FlashType::SUCCESS,
            );
        } catch (EmailNotSentException $e) {
            Debugger::log($e, Debugger::ERROR);
            $this->getPresenter()->flashMessage(
                'Rezervace byla úspěšně vytvořena, ale nastal problém při odesílání potvrzovacího e-mailu.',
                FlashType::WARNING
            );
        } catch (ReservationInPastException) {
            $this->getPresenter()->flashMessage('Termín rezervace musí být v budoucnu', FlashType::ERROR);
        } catch (CapacityExceededException) {
            $this->getPresenter()->flashMessage('Překročena maximální kapacita jeskyně', FlashType::ERROR);
        } catch (Exception $e) {
            Debugger::log($e, Debugger::CRITICAL);
            $this->getPresenter()->flashMessage($this->translator->trans('flash.oops'), FlashType::ERROR);
        }

        $this->getPresenter()->redirect('this');
    }

    private function mapReservationData(ArrayHash $values): CreateReservationData
    {
        return new CreateReservationData(
            date: new DateTime((string) $values->date),
            count: (int) $values->count,
            hasChildren: (bool) $values->has_children,
            comment: (string) ($values->comment ?? ''),
            userId: isset($values->user_id) ? (int) $values->user_id : null,
            email: isset($values->email) ? (string) $values->email : null,
            name: isset($values->name) ? (string) $values->name : null,
            telephone: isset($values->telephone) ? (string) $values->telephone : null,
        );
    }
}
