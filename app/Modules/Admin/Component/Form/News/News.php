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
        private readonly Translator  $translator,
        private readonly NewsManager $newsManager)
    {
    }


    public function createComponentForm(): Form
    {
        $form = new Form();
        $form->addText('title', $this->translator->trans('forms.header'))
            ->setRequired()
            ->setMaxLength(50)
            ->setHtmlAttribute('class', 'form-control');
        $form->addSelect('onHomepage', $this->translator->trans('forms.showNewsHomepage'), [false => 'Ne', true => 'Ano'])
            ->setHtmlAttribute('class', 'form-control')
            ->setDefaultValue(true);
        $form->addTextArea('text', 'Text')
            ->setHtmlAttribute('id', 'textarea');
        $form->addSubmit('confirm', $this->translator->trans('button.addNews'))
            ->setHtmlAttribute('style', 'float: right;')
            ->setHtmlAttribute('class', 'btn btn-info');
        $form->onSuccess[] = [$this, 'formOnSuccess'];

        return $form;
    }


    /**
     * @throws AbortException
     */
    public function formOnSuccess(Form $form, $values): void
    {
        try {
            $this->newsManager->insert($values->title, (bool)$values->onHomepage, $values->text);
            $this->getPresenter()->flashMessage($this->translator->trans('flash.newsAdded'), FlashType::SUCCESS);
        } catch (\Exception $exception) {
            $this->getPresenter()->flashMessage($this->translator->trans("flash.oops"), FlashType::ERROR);
        }
        $this->getPresenter()->redirect('News:');
    }
}
