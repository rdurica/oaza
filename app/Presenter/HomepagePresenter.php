<?php

declare(strict_types=1);

namespace App\Presenter;

use App\Modules\Admin\Manager\NewsManager;
use Nette\DI\Attributes\Inject;

/**
 * HomepagePresenter
 *
 * @package   App\Presenter
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class HomepagePresenter extends Presenter
{
    #[Inject]
    public NewsManager $newsManager;


    /**
     * Fetch last new to homepage.
     *
     * @return void
     */
    public function renderDefault(): void
    {
        $this->getTemplate()->lastNews = $this->newsManager->findHomepageNews()->fetch();
    }
}
