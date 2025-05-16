<?php

namespace App\Presenter;

use App\Util\FlashType;
use Nette\Application\AbortException;

/**
 * Base presenter for presenters with restricted access. User should be logged-in.
 * All presenters with this condition must extend this one.
 *
 * @package   App\Presenter
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
abstract class SecurePresenter extends Presenter
{
    /**
     * Checks if user is logged-in.
     *
     * @return void
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
