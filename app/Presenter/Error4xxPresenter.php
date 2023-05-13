<?php

namespace App\Presenter;

use Nette;
use Nette\Application\BadRequestException;
use Nette\Application\Request;

/**
 * Error4xxPresenter
 *
 * @package   App\Presenter
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class Error4xxPresenter extends Presenter
{

    /**
     * Startup.
     *
     * @return void
     * @throws Nette\Application\AbortException
     * @throws BadRequestException
     */
    public function startup(): void
    {
        parent::startup();
        if (!$this->getRequest()->isMethod(Request::FORWARD)) {
            $this->error();
        }
    }


    /**
     * Render error page.
     *
     * @param BadRequestException $exception
     * @return void
     */
    public function renderDefault(BadRequestException $exception): void
    {
        // load template 403.latte or 404.latte or ... 4xx.latte
        $file = __DIR__ . "/templates/Error/{$exception->getCode()}.latte";
        $this->getTemplate()->setFile(is_file($file) ? $file : __DIR__ . '/templates/Error/4xx.latte');
    }
}
