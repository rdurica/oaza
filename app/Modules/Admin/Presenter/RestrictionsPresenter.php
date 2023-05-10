<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenter;

use App\Modules\Admin\Component\Form\Restriction\IRestriction as RestrictionFormFactory;
use App\Modules\Admin\Component\Form\Restriction\Restriction as RestrictionForm;
use App\Modules\Admin\Component\Grid\Restriction\IRestriction as RestrictionGridFactory;
use App\Modules\Admin\Component\Grid\Restriction\Restriction;
use Nette\DI\Attributes\Inject;

class RestrictionsPresenter extends AdminPresenter
{
    #[Inject]
    public RestrictionGridFactory $restrictionGrid;

    #[Inject]
    public RestrictionFormFactory $restrictionForm;

    public function createComponentGrid(): Restriction
    {
        return $this->restrictionGrid->create();
    }

    public function createComponentForm(): RestrictionForm
    {
        return $this->restrictionForm->create();
    }
}
