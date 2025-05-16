<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenter;

use App\Component\Form\News\News as NewsForm;
use App\Component\Form\News\NewsFormFactory as NewsFormFactory;
use App\Component\Grid\News\News;
use App\Component\Grid\News\NewsGridFactory as NewsGridFactory;
use App\Modules\Admin\Manager\NewsManager;
use App\Util\FlashType;
use Nette\Application\AbortException;
use Nette\DI\Attributes\Inject;

/**
 * NewsPresenter
 *
 * @package   App\Modules\Admin\Presenter
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class NewsPresenter extends AdminPresenter
{
    #[Inject]
    public NewsGridFactory $newsGrid;

    #[Inject]
    public NewsFormFactory $newsForm;

    #[Inject]
    public NewsManager $newsManager;

    public ?int $newsId = null;

    /**
     * Create news grid.
     *
     * @return News
     */
    public function createComponentGrid(): News
    {
        return $this->newsGrid->create();
    }

    /**
     * Create news form.
     *
     * @return NewsForm
     */
    public function createComponentForm(): NewsForm
    {
        return $this->newsForm->create($this->newsId);
    }

    /**
     * Edit news (validate if exact news exists)
     *
     * @param int $id
     * @return void
     * @throws AbortException
     */
    public function renderEdit(int $id): void
    {
        $this->newsId = $id;
        $data = $this->newsManager->getEntityTable()->where("id = ?", $id)->fetch();

        if (!$data) {
            $this->getPresenter()
                ->flashMessage($this->translator->trans("flash.newsDoesNotExists"), FlashType::WARNING);
            $this->getPresenter()->redirect("News:");
        }
    }
}
