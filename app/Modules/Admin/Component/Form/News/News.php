<?php

declare(strict_types=1);

namespace App\Modules\Admin\Component\Form\News;

use App\Component\Component;
use App\Modules\Admin\Manager\NewsManager;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * News form.
 *
 * @package   App\Modules\Admin\Component\Form\News
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class News extends Component
{
    /**
     * Constructor.
     *
     * @param int|null    $id
     * @param Translator  $translator
     * @param NewsManager $newsManager
     */
    public function __construct(
        public ?int $id,
        private readonly Translator $translator,
        private readonly NewsManager $newsManager
    ) {
    }


    /**
     * Create News form.
     *
     * @return Form
     */
    public function createComponentForm(): Form
    {
        $yesNo = [0 => 'Ne', 1 => 'Ano'];

        $form = new Form();
        $form->addHidden("id");
        $form->addTextArea('text', $this->translator->trans('forms.news'))
            ->setHtmlAttribute('id', 'textarea');
        $form->addSelect('display_on_homepage', $this->translator->trans('forms.newsOnHomepage'), $yesNo)
            ->setHtmlAttribute('class', 'form-control')
            ->setDefaultValue(1);
        $form->addSelect('is_draft', $this->translator->trans('forms.isDraft'), $yesNo)
            ->setHtmlAttribute('class', 'form-control')
            ->setDefaultValue(0);
        $form->addSubmit('confirm', $this->translator->trans('button.save'))
            ->setHtmlAttribute('style', 'float: right;')
            ->setHtmlAttribute('class', 'btn btn-info');
        $form->onSuccess[] = [$this, 'onSuccess'];

        if ($this->id) {
            $form->setDefaults($this->newsManager->find($this->id));
        }

        return $form;
    }


    /**
     * Process News form.
     *
     * @param Form      $form
     * @param ArrayHash $values
     * @return void
     * @throws AbortException
     */
    public function onSuccess(Form $form, ArrayHash $values): void
    {
        try {
            $this->newsManager->save(
                $values->text,
                boolval($values->display_on_homepage),
                boolval($values->is_draft),
                intval($values->id)
            );
            $this->getPresenter()->flashMessage($this->translator->trans('flash.newsSaved'), FlashType::SUCCESS);
        } catch (\Exception $exception) {
            $this->getPresenter()->flashMessage($this->translator->trans("flash.oops"), FlashType::ERROR);
        }
        $this->getPresenter()->redirect('News:');
    }
}
