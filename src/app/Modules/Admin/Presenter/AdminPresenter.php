<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenter;

use App\Presenter\Presenter;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Nette\Application\AbortException;
use Nette\DI\Attributes\Inject;

/**
 * Base presenter for admin module. All presenters should extend this due to rights check.
 *
 * @package   App\Modules\Admin\Presenter
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
abstract class AdminPresenter extends Presenter
{
    #[Inject]
    public Translator $translator;

    /**
     * Check if user is logged in and have correct role.
     * @return void
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
