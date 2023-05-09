<?php

declare(strict_types=1);

namespace App\Modules\Admin\Component\Form\News;

use App\Component\Component;
use App\Model\Manager\NewsManager;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;

/**
 * Class CreateNews
 * @package Oaza\Form
 */
class News extends Component
{
    public function __construct(
        public ?int $id,
        private readonly Translator $translator,
        private readonly NewsManager $newsManager
    ) {
    }


    public function createComponentForm(): Form
    {
        $form = new Form();
        $form->addHidden("id");
        $form->addText('name', $this->translator->trans('forms.header'))
            ->setRequired()
            ->setMaxLength(50)
            ->setHtmlAttribute('class', 'form-control');
        $form->addSelect('show_homepage', $this->translator->trans('forms.showNewsHomepage'), [0 => 'Ne', 1 => 'Ano'])
            ->setHtmlAttribute('class', 'form-control')
            ->setDefaultValue(true);
        $form->addTextArea('text', 'Text')
            ->setHtmlAttribute('id', 'textarea');
        $form->addSubmit('confirm', $this->translator->trans('button.save'))
            ->setHtmlAttribute('style', 'float: right;')
            ->setHtmlAttribute('class', 'btn btn-info');
        $form->onSuccess[] = [$this, 'formOnSuccess'];

        if ($this->id) {
            $data = $this->newsManager->getEntityTable()->where("id = ?", $this->id)->fetch();
            $form->setDefaults($data);
        }

        return $form;
    }


    /**
     * @throws AbortException
     */
    public function formOnSuccess(Form $form, $values): void
    {
        try {
            $this->newsManager->save((int)$values->id, $values->name, (bool)$values->show_homepage, $values->text);
            $this->getPresenter()->flashMessage($this->translator->trans('flash.newsSaved'), FlashType::SUCCESS);
        } catch (\Exception $exception) {
            $this->getPresenter()->flashMessage($this->translator->trans("flash.oops"), FlashType::ERROR);
        }
        $this->getPresenter()->redirect('News:');
    }
}
