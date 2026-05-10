<?php

declare(strict_types=1);

namespace App\Component\Form\RestrictionEdit;

use App\Component\Component;
use App\Facade\RestrictionFacade;
use App\Mapper\CreateRestrictionDtoMapper;
use App\Model\Manager\RestrictionManager;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use DateMalformedStringException;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\UI\Form;
use Nette\Utils\DateTime;

/**
 * Restriction edit form.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
final class RestrictionEdit extends Component
{
    private const string DATE_FORMAT = 'd.m.Y';

    public function __construct(
        public ?int $restrictionId,
        private readonly RestrictionManager $restrictionManager,
        private readonly RestrictionFacade $restrictionFacade,
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
        $form->addText('from', 'Od')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired();
        $form->addText('to', 'Do')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired();
        $form->addTextArea('message', 'Zpráva')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('id', 'textarea');
        $form->addSubmit('save', 'Uložit')
            ->setHtmlAttribute('class', 'btn btn-info admin-form-submit');

        $form->onValidate[] = [$this, 'onValidate'];
        $form->onSuccess[] = [$this, 'onSuccess'];

        if ($this->restrictionId) {
            $data = $this->restrictionManager->findById($this->restrictionId);
            if ($data) {
                $form->setDefaults([
                    'id' => $data->id,
                    'from' => DateTime::from($data->from)->format(self::DATE_FORMAT),
                    'to' => DateTime::from($data->to)->format(self::DATE_FORMAT),
                    'message' => $data->message,
                ]);
            }
        }

        return $form;
    }

    /**
     * @param array{from?: string, to?: string} $data
     */
    public function onValidate(Form $form, array $data): void
    {
        $from = $this->parseDate($data['from'] ?? null);
        $to = $this->parseDate($data['to'] ?? null);

        if ($from === null) {
            $form['from']->addError('Datum musí být ve formátu DD.MM.RRRR.');
        }

        if ($to === null) {
            $form['to']->addError('Datum musí být ve formátu DD.MM.RRRR.');
        }

        if ($from !== null && $to !== null && $to < $from) {
            $form->addError('Datum "Do" nesmí být dříve než datum "Od".');
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
        try {
            $createRestrictionDto = CreateRestrictionDtoMapper::fromFormData($data);
            $restrictionId = (int) ($data['id'] ?? 0);
            $this->restrictionFacade->update($restrictionId, $createRestrictionDto);

            $this->presenter->flashMessage('Omezení provozu bylo upraveno', FlashType::SUCCESS);
        } catch (DateMalformedStringException) {
            $this->getPresenter()->flashMessage('Neplatný formát data. Použijte DD.MM.RRRR.', FlashType::ERROR);
            $this->presenter->redirect('this');
        } catch (Exception) {
            $this->getPresenter()->flashMessage($this->translator->trans('flash.oops'), FlashType::ERROR);
            $this->presenter->redirect('this');
        }

        $this->presenter->redirect('Restrictions:');
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
