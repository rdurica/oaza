<?php declare(strict_types=1);

namespace App\Presenter;

use App\Util\FlashType;
use Nette\Application\AbortException;

/**
 * Base presenter for presenters with restricted access. User should be logged-in.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
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
        if (!$this->getUser()->isLoggedIn())
        {
            $this->flashMessage('Musíte být přihlášen', FlashType::ERROR);
            $this->redirect(':Homepage:');
        }
    }
}
