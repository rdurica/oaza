<?php

namespace App\Presenter;

use App\Util\FlashType;
use Nette\Application\AbortException;

/**
 * Class SecurePresenter
 * @package App\Presenters
 */
abstract class SecurePresenter extends Presenter
{
    /**
     * Permissions check
     * @throws AbortException
     */
    public function startup(): void
    {
        parent::startup();
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage('Musíte být přihlášen', FlashType::ERROR);
            $this->redirect(':Homepage:');
        }
    }
}
