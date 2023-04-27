<?php

namespace Oaza\Grids;

use App\Models\RestrictionManager;
use Nette\Application\UI\Control;
use Ublaboo\DataGrid\DataGrid;

/**
 * Class User
 * @package Oaza\Grid
 */
class RestrictionsGrid extends Control
{
    /** @var RestrictionManager */
    private $restrictionModel;


    /**
     * User constructor.
     * @param RestrictionManager $restrictionModel
     * @internal param UserManager $userModel
     */
    public function __construct(RestrictionManager $restrictionModel)
    {

        $this->restrictionModel = $restrictionModel;
    }


    /**
     * User grid
     * @return DataGrid
     */
    public function createComponentGrid()
    {
        $grid = new DataGrid();
        $grid->setDataSource($this->restrictionModel->getEntitiyTable()->order('id DESC'));
        $grid->addColumnDateTime('from', 'Od')
            ->setFormat('j. n. Y', 'd. m. yyyy')
            ->setFilterDate();
        $grid->addColumnDateTime('to', 'Do')
            ->setFormat('j. n. Y', 'd. m. yyyy')
            ->setFilterDate();
        $grid->addColumnText('message', 'Zpráva')
            ->setFilterText();

        $grid->addAction('delete', 'Smazat', 'delete!')
            ->setIcon('trash')
            ->setClass('btn btn-danger btn-xs')
            ->setConfirm(function ($item) {
                return 'Naozaj chcete smazat omezení %s?';
            }, 'message');
        $grid->setOuterFilterRendering();

        return $grid;
    }

    /**
     * Delete user handler
     * @param int $id
     */
    public function handleDelete($id)
    {
        $this->restrictionModel->getEntitiyTable()->where('id', $id)->delete();
        $this->presenter->flashMessage('Omezení smazáno', 'success');
        $this->presenter->redirect('this');
    }


    /**
     * Render grid
     */
    public function render()
    {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->data = $this->restrictionModel->getEntitiyTable()->order('id DESC')->fetchAll();
        $this->template->render();
    }
}
