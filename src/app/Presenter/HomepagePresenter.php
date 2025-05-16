<?php declare(strict_types=1);

namespace App\Presenter;

use App\Model\Manager\NewsManager;

/**
 * HomepagePresenter.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
class HomepagePresenter extends Presenter
{
    /**
     * Constructor.
     *
     * @param NewsManager $newsManager
     */
    public function __construct(private readonly NewsManager $newsManager)
    {
        parent::__construct();
    }

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
