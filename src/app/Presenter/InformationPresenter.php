<?php

declare(strict_types=1);

namespace App\Presenter;

use App\Model\Entity\SodexoPass;
use App\Modules\Admin\Manager\NewsManager;
use Nette\DI\Attributes\Inject;

/**
 * InformationPresenter
 *
 * @package   App\Presenter
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class InformationPresenter extends Presenter
{
    #[Inject]
    public NewsManager $newsManager;


    /**
     * Create sodexo pass.
     *
     * @return void
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
     * Render last 10 news.
     *
     * @return void
     */
    public function renderNews(): void
    {
        $dataSource = $this->newsManager->getEntityTable()->limit(10);
        $this->getTemplate()->news = $dataSource;
    }
}
