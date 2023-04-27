<?php

namespace App\Presenter;

use Nette;

/**
 * Class Error4xxPresenter
 * @package App\Presenters
 */
class Error4xxPresenter extends Presenter
{
    /**
     * Startup
     */
    public function startup(): void
    {
        parent::startup();
        if (!$this->getRequest()->isMethod(Nette\Application\Request::FORWARD)) {
            $this->error();
        }
    }


    /**
     * Render exception
     * @param Nette\Application\BadRequestException $exception
     */
    public function renderDefault(Nette\Application\BadRequestException $exception)
    {
        // load template 403.latte or 404.latte or ... 4xx.latte
        $file = __DIR__ . "/templates/Error/{$exception->getCode()}.latte";
        $this->template->setFile(is_file($file) ? $file : __DIR__ . '/templates/Error/4xx.latte');
    }
}
