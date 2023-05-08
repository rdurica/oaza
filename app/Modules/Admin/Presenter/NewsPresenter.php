<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenter;

use App\Modules\Admin\Component\Grid\News\INews;
use App\Modules\Admin\Component\Grid\News\News;
use Nette\DI\Attributes\Inject;

class NewsPresenter extends AdminPresenter
{

    #[Inject]
    public INews $newsGrid;

    public function createComponentGrid(): News
    {
        return $this->newsGrid->create();
    }
}
