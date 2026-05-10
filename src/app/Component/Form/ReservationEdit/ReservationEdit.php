<?php

declare(strict_types=1);

namespace App\Component\Form\ReservationEdit;

use App\Component\Component;
use App\Exception\CapacityExceededException;
use App\Exception\NotAllowedOperationException;
use App\Model\Manager\ReservationManager;
use App\Model\Service\ReservationCalendarService;
use App\Model\Service\ReservationService;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Exception;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Mail\SmtpException;
use Nette\Utils\DateTime;

/**
 * Reservation edit form.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
class ReservationEdit extends Component
{
    private const int LOOKAHEAD_DAYS = 100;

    public function __construct(
        public ?int $reservationId,
        private readonly ReservationManager $reservationManager,
        private readonly ReservationService $reservationService,
        private readonly Translator $translator,
    ) {
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
        $form->addInteger('count', 'Počet osob')
            ->setRequired()
            ->setHtmlAttribute('class', 'form-control')
            ->addRule(Form::RANGE, 'Počet osob musí být mezi 1 a 5.', [1, 5]);
        $form->addSelect('date_day', 'Datum', $this->generateDays())
            ->setRequired()
            ->setHtmlAttribute('class', 'form-control');
        $form->addSelect('date_hour', 'Hodina', $this->generateHours())
            ->setRequired()
            ->setHtmlAttribute('class', 'form-control');
        $form->addHidden('date_minute', '00');
        $form->addCheckbox('has_children', 'Děti');
        $form->addHidden('confirmAction');
        $form->addSubmit('save', 'Uložit')
            ->setHtmlAttribute('class', 'btn btn-info admin-form-submit');

        $form->onValidate[] = [$this, 'onValidate'];
        $form->onSuccess[] = [$this, 'onSuccess'];

        if ($this->reservationId) {
            $data = $this->reservationManager->findById($this->reservationId);
            if ($data) {
                $date = DateTime::from($data->date);
                $form->setDefaults([
                    'id' => $data->id,
                    'count' => $data->count,
                    'date_day' => $date->format('Y-m-d'),
                    'date_hour' => $date->format('H'),

                    'has_children' => (bool) $data->has_children,
                ]);
            }
        }

        return $form;
    }

    /**
     * @param array{id?: string, count?: string, date_day?: string, date_hour?: string, date_minute?: string, has_children?: string} $data
     */
    public function onValidate(Form $form, array $data): void
    {
        $date = $this->buildDateTime($data);

        if ($date === null) {
            /** @var \Nette\Forms\Controls\BaseControl $control */
            $control = $form['date_day'];
            $control->addError('Neplatný termín.');
            return;
        }

        if ($date <= new DateTime()) {
            /** @var \Nette\Forms\Controls\BaseControl $control */
            $control = $form['date_day'];
            $control->addError('Termín musí být v budoucnosti.');
            return;
        }

        $dayOfWeek = (int) $date->format('N');
        if ($dayOfWeek >= 6) {
            /** @var \Nette\Forms\Controls\BaseControl $control */
            $control = $form['date_day'];
            $control->addError('Víkendové termíny nejsou povoleny.');
            return;
        }

        $hour = (int) $date->format('H');
        if (!in_array($hour, ReservationCalendarService::getSlotHours(), true)) {
            /** @var \Nette\Forms\Controls\BaseControl $control */
            $control = $form['date_hour'];
            $control->addError('Neplatná hodina rezervace.');
            return;
        }

        $minute = (int) $date->format('i');
        if ($minute !== 0) {
            /** @var \Nette\Forms\Controls\BaseControl $control */
            $control = $form['date_minute'];
            $control->addError('Minuty musí být 00.');
            return;
        }

        $reservationId = (int) ($data['id'] ?? 0);
        if ($reservationId <= 0) {
            $form->addError('Neplatná rezervace.');
            return;
        }

        $reservation = $this->reservationManager->findById($reservationId);
        if ($reservation === null) {
            $form->addError('Rezervace neexistuje.');
            return;
        }

    }

    /**
     * Process form.
     *
     * @param Form  $form
     * @param array $values
     *
     * @return void
     * @throws AbortException
     */
    public function onSuccess(Form $form, array $values): void
    {
        $reservationId = (int) $values['id'];
        $date = $this->buildDateTime($values);
        $count = (int) $values['count'];
        $hasChildren = (bool) ($values['has_children'] ?? false);
        $confirmAction = $values['confirmAction'] ?? '';

        $reservation = $this->reservationManager->findById($reservationId);
        if ($reservation !== null) {
            $oldDate = DateTime::from($reservation->date);
            $oldCount = (int) $reservation->count;
            $dateChanged = $date != $oldDate;
            $countChanged = $count !== $oldCount;
            $countIncreased = $countChanged && $count > $oldCount;

            if (($dateChanged || $countIncreased) && $confirmAction === '') {
                $collision = $this->reservationService->getCollisionData($reservationId, $date, $count);
                if ($collision !== null) {
                    $presenter = $this->getPresenter();
                    $presenter->template->showConfirmDialog = true;
                    $presenter->template->collisionData = $collision;
                    $presenter->template->reservationId = $reservationId;
                    return;
                }
            }
        }

        try {
            $this->reservationService->updateByAdmin($reservationId, $date, $count, $hasChildren, $confirmAction !== '' ? $confirmAction : null);
            $this->getPresenter()->flashMessage('Rezervace byla upravena.', FlashType::SUCCESS);
        } catch (SmtpException) {
            $this->getPresenter()->flashMessage(
                'Nastal problém při odesílání informačního e-mailu.',
                FlashType::WARNING,
            );
        } catch (CapacityExceededException) {
            $this->getPresenter()->flashMessage(
                'Vybraný termín není volný.',
                FlashType::WARNING,
            );
            $this->getPresenter()->redirect('this');
        } catch (NotAllowedOperationException) {
            $this->presenter->flashMessage($this->translator->trans('flash.operationNotAllowed'), FlashType::ERROR);
            $this->getPresenter()->redirect('this');
        } catch (Exception) {
            $this->presenter->flashMessage($this->translator->trans('flash.oops'), FlashType::ERROR);
            $this->getPresenter()->redirect('this');
        }

        $this->getPresenter()->redirect('Reservations:');
    }

    /**
     * @param array{date_day?: string, date_hour?: string, date_minute?: string} $data
     */
    private function buildDateTime(array $data): ?DateTime
    {
        $day = $data['date_day'] ?? null;
        $hour = $data['date_hour'] ?? null;
        $minute = $data['date_minute'] ?? '00';

        if ($day === null || $hour === null) {
            return null;
        }

        $dateString = $day . ' ' . str_pad((string) $hour, 2, '0', \STR_PAD_LEFT) . ':' . $minute . ':00';
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $dateString);

        if ($date === false) {
            return null;
        }

        return DateTime::from($date);
    }

    /**
     * @return array<string, string>
     */
    private function generateDays(): array
    {
        $days = [];
        $now = new DateTime();

        for ($i = 0; $i < self::LOOKAHEAD_DAYS; $i++) {
            $date = (clone $now)->modify("+{$i} days");
            $dayOfWeek = (int) $date->format('N');

            if ($dayOfWeek >= 6) {
                continue;
            }

            $key = $date->format('Y-m-d');
            $label = $date->format('j.n.Y');
            $days[$key] = $label;
        }

        return $days;
    }

    /**
     * @return array<string, string>
     */
    private function generateHours(): array
    {
        $hours = [];

        foreach (ReservationCalendarService::getSlotHours() as $hour) {
            $key = str_pad((string) $hour, 2, '0', \STR_PAD_LEFT);
            $hours[$key] = $key . ':00';
        }

        return $hours;
    }
}
