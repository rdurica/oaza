<?php

declare(strict_types=1);

namespace App\Presenter;

use App\Model\Entity\SodexoPass;
use App\Model\Manager\NewsManager;
use Nette\DI\Attributes\Inject;

class InformationPresenter extends Presenter
{
    #[Inject]
    public NewsManager $newsManager;


    /**
     * Render Pass page
     */
    public function renderPass(): void
    {
        $this->getTemplate()->sodexo = [
            SodexoPass::create("Bonus"),
            SodexoPass::create("Darkovy"),
            SodexoPass::create("Flexi"),
            SodexoPass::create("Fokus"),
            SodexoPass::create("Relax"),
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
