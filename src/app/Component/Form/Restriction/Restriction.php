<?php

declare(strict_types=1);

namespace App\Component\Form\Restriction;

use App\Component\Component;
use App\Dto\CreateRestrictionDto;
use App\Facade\RestrictionFacade;
use App\Mapper\CreateRestrictionDtoMapper;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;

/**
 * Restriction form.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
final class Restriction extends Component
{
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
     * @param Form  $form
     * @param array $data
     *
     * @return void
     * @throws \DateMalformedStringException
     */
    #[NoReturn]
    public function onSuccess(Form $form, array $data): void
    {
        $createRestrictionDto = CreateRestrictionDtoMapper::fromFormData($data);

        try
        {
            $this->restrictionFacade->create($createRestrictionDto);

            $this->presenter->flashMessage('Omezení provozu přidáno', FlashType::SUCCESS);
        }
        catch (Exception)
        {
            $this->getPresenter()->flashMessage($this->translator->trans('flash.oops'), FlashType::ERROR);
        }

        $this->presenter->redirect('Restrictions:');
    }
}
