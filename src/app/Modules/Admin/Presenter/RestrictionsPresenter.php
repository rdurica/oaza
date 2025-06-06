<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenter;

use App\Component\Form\Restriction\Restriction as RestrictionForm;
use App\Component\Form\Restriction\RestrictionFormFactory as RestrictionFormFactory;
use App\Component\Grid\Restriction\RestrictionGridFactory as RestrictionGridFactory;
use App\Component\Grid\Restriction\Restriction;
use Nette\DI\Attributes\Inject;

/**
 * RestrictionsPresenter
 *
 * @package   App\Modules\Admin\Presenter
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class RestrictionsPresenter extends AdminPresenter
{
    #[Inject]
    public RestrictionGridFactory $restrictionGrid;

    #[Inject]
    public RestrictionFormFactory $restrictionForm;

    /**
     * Create restriction grid.
     *
     * @return Restriction
     */
    public function createComponentGrid(): Restriction
    {
        return $this->restrictionGrid->create();
    }

    /**
     * Create restriction form.
     *
     * @return RestrictionForm
     */
    public function createComponentForm(): RestrictionForm
    {
        return $this->restrictionForm->create();
    }
}
