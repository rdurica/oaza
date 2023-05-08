<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenter;

use App\Modules\Admin\Component\Grid\Restriction\IRestriction;
use App\Modules\Admin\Component\Grid\Restriction\Restriction;
use Nette\DI\Attributes\Inject;

class RestrictionsPresenter extends AdminPresenter
{
    #[Inject]
    public IRestriction $restrictionGrid;

    public function createComponentGrid(): Restriction
    {
        return $this->restrictionGrid->create();
    }
}
