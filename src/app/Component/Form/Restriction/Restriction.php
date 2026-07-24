<?php

declare(strict_types=1);

namespace App\Component\Form\Restriction;

use App\Component\Component;
use App\Facade\RestrictionFacade;
use App\Mapper\CreateRestrictionDtoMapper;
use App\Model\Service\ReservationCalendarService;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use DateMalformedStringException;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\DateTime;

/**
 * Restriction form.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
final class Restriction extends Component
{
    private const string DATE_FORMAT = 'd.m.Y';

    /**
     * Constructor.
     *
     * @param RestrictionFacade $restrictionFacade
     * @param Translator        $translator
     */
    public function __construct(
        private readonly RestrictionFacade $restrictionFacade,
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
        $hourItems = $this->getHourItems();

        $form->addRadioList('mode', 'Typ omezení', [
            CreateRestrictionDtoMapper::MODE_FULL_DAYS => 'Celé dny (od–do)',
            CreateRestrictionDtoMapper::MODE_PART_DAY => 'Část dne',
        ])
            ->setDefaultValue(CreateRestrictionDtoMapper::MODE_FULL_DAYS)
            ->setRequired();

        $form->addText('from', 'Datum od')
            ->setHtmlAttribute('class', 'form-control');
        $form->addText('to', 'Datum do')
            ->setHtmlAttribute('class', 'form-control');
        $form->addText('date', 'Datum')
            ->setHtmlAttribute('class', 'form-control');
        $form->addSelect('timeFrom', 'Hodina od', $hourItems)
            ->setPrompt('—')
            ->setHtmlAttribute('class', 'form-control');
        $form->addSelect('timeTo', 'Hodina do (včetně)', $hourItems)
            ->setPrompt('—')
            ->setHtmlAttribute('class', 'form-control');
        $form->addTextArea('message', 'Zpráva')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('id', 'textarea');
        $form->addSelect('showNewsOnHomepage', $this->translator->trans('forms.showNewsHomepage'), [0 => 'Ne', 1 => 'Ano'])
            ->setHtmlAttribute('class', 'form-control')
            ->setDefaultValue(0);
        $form->addSubmit('save', 'Uložit')
            ->setHtmlAttribute('class', 'btn btn-info admin-form-submit');

        $form->onValidate[] = [$this, 'onValidate'];
        $form->onSuccess[] = [$this, 'onSuccess'];

        return $form;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function onValidate(Form $form, array $data): void
    {
        $mode = (string) ($data['mode'] ?? CreateRestrictionDtoMapper::MODE_FULL_DAYS);

        if ($mode === CreateRestrictionDtoMapper::MODE_PART_DAY) {
            $date = $this->parseDate(isset($data['date']) ? (string) $data['date'] : null);
            $timeFrom = $data['timeFrom'] ?? null;
            $timeTo = $data['timeTo'] ?? null;

            if ($date === null) {
                $this->addFieldError($form, 'date', 'Datum musí být ve formátu DD.MM.RRRR.');
            }

            if ($timeFrom === null || $timeFrom === '') {
                $this->addFieldError($form, 'timeFrom', 'Vyberte hodinu od.');
            }

            if ($timeTo === null || $timeTo === '') {
                $this->addFieldError($form, 'timeTo', 'Vyberte hodinu do.');
            }

            if (
                $timeFrom !== null && $timeFrom !== ''
                && $timeTo !== null && $timeTo !== ''
                && (int) $timeFrom > (int) $timeTo
            ) {
                $form->addError('Hodina „do“ nesmí být dříve než hodina „od“.');
            }

            return;
        }

        $from = $this->parseDate(isset($data['from']) ? (string) $data['from'] : null);
        $to = $this->parseDate(isset($data['to']) ? (string) $data['to'] : null);

        if ($from === null) {
            $this->addFieldError($form, 'from', 'Datum musí být ve formátu DD.MM.RRRR.');
        }

        if ($to === null) {
            $this->addFieldError($form, 'to', 'Datum musí být ve formátu DD.MM.RRRR.');
        }

        if ($from !== null && $to !== null && $to < $from) {
            $form->addError('Datum „Do“ nesmí být dříve než datum „Od“.');
        }
    }

    /**
     * Process form.
     *
     * @param Form  $form
     * @param array $data
     *
     * @return void
     */
    #[NoReturn]
    public function onSuccess(Form $form, array $data): void
    {
        try
        {
            $createRestrictionDto = CreateRestrictionDtoMapper::fromFormData($data);
            $this->restrictionFacade->create($createRestrictionDto);

            $this->getPresenter()->flashMessage('Omezení provozu přidáno', FlashType::SUCCESS);
        }
        catch (DateMalformedStringException)
        {
            $this->getPresenter()->flashMessage('Neplatný formát data nebo hodin.', FlashType::ERROR);
            $this->getPresenter()->redirect('this');
        }
        catch (Exception)
        {
            $this->getPresenter()->flashMessage($this->translator->trans('flash.oops'), FlashType::ERROR);
        }

        $this->getPresenter()->redirect('Restrictions:');
    }

    /**
     * @return array<int, string>
     */
    private function getHourItems(): array
    {
        $items = [];
        foreach (ReservationCalendarService::getSlotHours() as $hour) {
            $items[$hour] = sprintf('%02d:00', $hour);
        }

        return $items;
    }

    private function addFieldError(Form $form, string $name, string $message): void
    {
        $control = $form[$name];
        if ($control instanceof BaseControl) {
            $control->addError($message);
        }
    }

    private function parseDate(?string $value): ?DateTime
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $date = DateTime::createFromFormat('!' . self::DATE_FORMAT, $value);
        if ($date === false || $date->format(self::DATE_FORMAT) !== $value) {
            return null;
        }

        return DateTime::from($date);
    }
}
