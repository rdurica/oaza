<?php

declare(strict_types=1);

namespace App\Presenter;

use AlesWita\Components\VisualPaginator;
use App\Model\Manager\NewsManager;
use App\Util\SodexoPass;
use Nette\DI\Attributes\Inject;

class InformationPresenter extends Presenter
{
    #[Inject]
    public NewsManager $newsManager;


    /**
     * Render Pass page
     */
    public function renderPass()
    {
        $this->getTemplate()->sodexo = [
            new SodexoPass("Bonus", "/assets/images/sodexo/Bonus.jpg"),
            new SodexoPass("Darkovy", "/assets/images/sodexo/Darkovy.jpg"),
            new SodexoPass("Flexi", "/assets/images/sodexo/Flexi.jpg"),
            new SodexoPass("Fokus", "/assets/images/sodexo/Fokus.jpg"),
            new SodexoPass("Relax", "/assets/images/sodexo/Relax.jpg"),
        ];
    }


    /**
     * Render news page
     */
    public function renderNews(): void
    {
        $dataSource = $this->newsManager->getPaginatorPosts(
            10,
            0
        );
        $this->getTemplate()->news = $dataSource;
    }
}
