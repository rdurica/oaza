<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenter;

use App\Component\Form\Restriction\Restriction as RestrictionForm;
use App\Component\Form\Restriction\RestrictionFormFactory as RestrictionFormFactory;
use App\Component\Form\RestrictionEdit\RestrictionEdit as RestrictionEditForm;
use App\Component\Form\RestrictionEdit\RestrictionEditFormFactory as RestrictionEditFormFactory;
use App\Component\Grid\Restriction\RestrictionGridFactory as RestrictionGridFactory;
use App\Component\Grid\Restriction\Restriction;
use App\Model\Manager\RestrictionManager;
use App\Util\FlashType;
use Nette\Application\AbortException;
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

    #[Inject]
    public RestrictionEditFormFactory $restrictionEditForm;

    #[Inject]
    public RestrictionManager $restrictionManager;

    public ?int $restrictionId = null;

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

    /**
     * Create restriction edit form.
     *
     * @return RestrictionEditForm
     */
    public function createComponentEditForm(): RestrictionEditForm
    {
        return $this->restrictionEditForm->create($this->restrictionId);
    }

    /**
     * Edit restriction.
     *
     * @param int $id
     *
     * @return void
     * @throws AbortException
     */
    public function renderEdit(int $id): void
    {
        $this->restrictionId = $id;
        $data = $this->restrictionManager->findById($id);

        if ($data === null) {
            $this->flashMessage($this->translator->trans('flash.operationNotAllowed'), FlashType::WARNING);
            $this->redirect('Restrictions:');
        }
    }
}
