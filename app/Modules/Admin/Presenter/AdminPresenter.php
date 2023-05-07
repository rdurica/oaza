<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenter;

use App\Presenter\Presenter;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Nette\Application\AbortException;
use Nette\DI\Attributes\Inject;

abstract class AdminPresenter extends Presenter
{
    #[Inject]
    public Translator $translator;

    /**
     * Check user rights
     * @throws AbortException
     */
    public function startup(): void
    {
        parent::startup();
        if (!$this->getUser()->isLoggedIn() && !$this->getUser()->isInRole('admin')) {
            $this->flashMessage($this->translator->trans("flash.notAuthorized"), FlashType::ERROR);
            $this->redirect(':Homepage:');
        }
    }
}
