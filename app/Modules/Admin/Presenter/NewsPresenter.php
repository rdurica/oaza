<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenter;

use App\Model\Manager\NewsManager;
use App\Modules\Admin\Component\Form\News\INews as NewsFormFactory;
use App\Modules\Admin\Component\Form\News\News as NewsForm;
use App\Modules\Admin\Component\Grid\News\INews as NewsGridFactory;
use App\Modules\Admin\Component\Grid\News\News as NewsGrid;
use App\Util\FlashType;
use Nette\Application\AbortException;
use Nette\DI\Attributes\Inject;

class NewsPresenter extends AdminPresenter
{
    #[Inject]
    public NewsGridFactory $newsGrid;

    #[Inject]
    public NewsFormFactory $newsForm;

    #[Inject]
    public NewsManager $newsManager;

    public ?int $newsId = null;

    public function createComponentGrid(): NewsGrid
    {
        return $this->newsGrid->create();
    }

    public function createComponentForm(): NewsForm
    {
        return $this->newsForm->create($this->newsId);
    }

    /**
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
