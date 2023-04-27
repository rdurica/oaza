<?php

declare(strict_types=1);

namespace App\Component\Form\CreateNews;

use App\Component\Component;
use App\Model\Manager\NewsManager;
use Contributte\Translation\Translator;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Class CreateNews
 * @package Oaza\Form
 */
class CreateNews extends Component
{
    public function __construct(private readonly Translator $translator, private readonly NewsManager $newsManager)
    {
    }


    /**
     * Create news form
     * @return Form
     */
    public function createComponentCreateNews()
    {
        $show = [0 => 'Ne', 1 => 'Ano'];

        $form = new Form();
        $form->addText('name', $this->translator->translate('oaza.forms.header'))
            ->setRequired()
            ->setMaxLength(50)
            ->setAttribute('class', 'form-control');
        $form->addSelect('show_homepage', $this->translator->translate('oaza.forms.showNewsHomepage'), $show)
            ->setAttribute('class', 'form-control')
            ->setDefaultValue(1);
        $form->addTextArea('text', 'Text')
            ->setAttribute('style', 'height: 250px;')
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'textarea');
        $form->addSubmit('confirm', $this->translator->translate('oaza.buttons.addNews'))
            ->setAttribute('class', 'btn btn-success');
        $form->onSuccess[] = [$this, 'formSucceed'];

        return $form;
    }


    /**
     * Form confirm
     * @param Form $form
     * @param ArrayHash $values
     */
    public function formSucceed(Form $form, $values)
    {
        try {
            $this->newsManager->insert($values);
            $this->presenter->flashMessage($this->translator->translate('oaza.messages.newsAdded'));
        } catch (\Exception $exception) {
            $this->presenter->flashMessage($exception->getMessage());
        }
        $this->presenter->redirect('this');
    }
}
