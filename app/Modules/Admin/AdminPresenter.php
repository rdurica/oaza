<?php

/**
 * Created by PhpStorm.
 * User: Home
 * Date: 15.10.2017
 * Time: 11:22
 */

namespace App\Modules\Admin;

use App\Presenter\Presenter;

/**
 * Class AdminPresenter
 */
abstract class AdminPresenter extends Presenter
{
    public function startup(): void
    {
        parent::startup();
        if (!$this->getUser()->isLoggedIn() && !$this->getUser()->isInRole('admin')) {
            $this->flashMessage('Nemate opravnení', 'danger');
            $this->redirect(':Homepage:');
        }
    }
}
