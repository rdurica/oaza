<?php

declare(strict_types=1);

namespace App\Presenter;

use App\Model\Manager\NewsManager;
use Nette\DI\Attributes\Inject;

class HomepagePresenter extends Presenter
{
    #[Inject]
    public NewsManager $newsManager;


    /**
     * News from db
     */
    public function renderDefault()
    {
        $this->getTemplate()->lastNews = $this->newsManager->findHomepageNews()->fetch();
    }
}
