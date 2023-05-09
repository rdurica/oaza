<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenter;

use App\Modules\Admin\Component\Form\News\INews as NewsFormFactory;
use App\Modules\Admin\Component\Form\News\News as NewsForm;
use App\Modules\Admin\Component\Grid\News\INews as NewsGridFactory;
use App\Modules\Admin\Component\Grid\News\News as NewsGrid;
use Nette\DI\Attributes\Inject;

class NewsPresenter extends AdminPresenter
{
    #[Inject]
    public NewsGridFactory $newsGrid;

    #[Inject]
    public NewsFormFactory $newsForm;

    public function createComponentGrid(): NewsGrid
    {
        return $this->newsGrid->create();
    }

    public function createComponentForm(): NewsForm
    {
        return $this->newsForm->create();
    }
}
