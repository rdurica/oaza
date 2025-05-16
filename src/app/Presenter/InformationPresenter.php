<?php declare(strict_types=1);

namespace App\Presenter;

use App\Model\Entity\SodexoPass;
use App\Model\Manager\NewsManager;

/**
 * InformationPresenter
 *
 * @package   App\Presenter
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class InformationPresenter extends Presenter
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
     * Create sodexo pass.
     *
     * @return void
     */
    public function renderPass(): void
    {
        $this->getTemplate()->sodexo = [
            SodexoPass::create('Bonus'),
            SodexoPass::create('Darkovy'),
            SodexoPass::create('Flexi'),
            SodexoPass::create('Fokus'),
            SodexoPass::create('Relax'),
        ];
    }

    /**
     * Render last 10 news.
     *
     * @return void
     */
    public function renderNews(): void
    {
        $dataSource = $this->newsManager->getEntityTable()->order('id DESC')->limit(10);
        $this->getTemplate()->news = $dataSource;
    }
}
