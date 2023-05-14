<?php

declare(strict_types=1);

namespace App\Modules\Admin\Component\Form\Restriction;

use App\Component\Component;
use App\Model\Manager\ReservationManager;
use App\Modules\Admin\Manager\NewsManager;
use App\Modules\Admin\Manager\RestrictionManager;
use App\Modules\Admin\Service\RestrictionService;
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
     * @param RestrictionService $restrictionService
     * @param Translator         $translator
     */
    public function __construct(
        private readonly RestrictionService $restrictionService,
        public readonly Translator $translator
    ) {
    }

    /**
     * Create Restriction form
     *
     * @return Form
     */
    public function createComponentForm(): Form
    {
        $yesNo = [0 => 'Ne', 1 => 'Ano'];
        $form = new Form();

        $form->addText('from', $this->translator->trans('forms.from'))
            ->setRequired(true);
        $form->addText('to', $this->translator->trans('forms.to'))
            ->setRequired(true);
        $form->addTextArea('text', $this->translator->trans('forms.news'))
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('id', 'textarea');
        $form->addSelect('createNews', $this->translator->trans('forms.createNews'), $yesNo)
            ->setDefaultValue(1);
        $form->addSelect('display_on_homepage', $this->translator->trans('forms.showNewsHomepage'), $yesNo)
            ->setDefaultValue(1);
        $form->addSubmit('save', $this->translator->trans('button.save'))
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
        try {
            $from = new DateTime($values->from);
            $to = new DateTime($values->to);
            $this->restrictionService->createRestriction(
                $from,
                $to,
                boolval($values->createNews),
                boolval($values->display_on_homepage),
                $values->text,
            );
            $this->presenter->flashMessage($this->translator->trans('flash.restrictionAdded'), FlashType::SUCCESS);
        } catch (\Exception $exception) {
            $this->presenter->flashMessage($this->translator->trans('flash.oops'), FlashType::ERROR);
        }

        $this->presenter->redirect('Restrictions:');
    }
}
