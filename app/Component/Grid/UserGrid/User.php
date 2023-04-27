<?php

namespace Oaza\Grids;

use App\Models\UserManager;
use Nette\Application\UI\Control;
use Ublaboo\DataGrid\DataGrid;

/**
 * Class User
 * @package Oaza\Grid
 */
class User extends Control
{
    /** @var  UserManager */
    public $userModel;


    /**
     * User constructor.
     * @param UserManager $userModel
     */
    public function __construct(UserManager $userModel)
    {
        $this->userModel = $userModel;
    }


    /**
     * User grid
     * @return DataGrid
     */
    public function createComponentGrid()
    {
        $status = ['Zakazan', 'Povolen',];

        $grid = new DataGrid();
        $grid->setDataSource($this->userModel->getEntityTable());
        $grid->addColumnText('name', 'Jméno')
            ->setFilterText();
        $grid->addColumnText('email', 'E-Mail')
            ->setFilterText();
        $grid->addColumnText('telephone', 'Telefon')
            ->setFilterText();
        $grid->addColumnDateTime('registered', 'Datum registrace')
            ->setFormat('j. n. Y', 'd. m. yyyy')
            ->setFilterDate();
        $grid->addColumnText('enabled', 'Status')
            ->setRenderer(function ($item) {
                if ($item->enabled === 1) {
                    return 'Povolen';
                } else {
                    return 'Zakazan';
                }
            });
        $grid->addAction('delete', 'Smazat', 'delete!')
            ->setIcon('trash')
            ->setClass('btn btn-danger btn-xs')
            ->setConfirm(function ($item) {
                return 'Naozaj chcete smazat uzivatela %s?';
            }, 'name');
        $grid->addAction('send', 'status', 'status!')
            ->setClass('btn btn-info btn-xs')
            ->setIcon('eye');
        $grid->addFilterSelect('enabled', 'Status', $status);
        $grid->setOuterFilterRendering();

        return $grid;
    }


    /**
     * Change enabled/disabled handler
     * @param int $id
     */
    public function handleStatus($id)
    {
        $this->userModel->changeStatus($id);
        $this->presenter->flashMessage('Uzivatel aktualizovan');
        $this->presenter->redirect('this');
    }


    /**
     * Delete user handler
     * @param int $id
     */
    public function handleDelete($id)
    {
        $this->userModel->delete($id);
        $this->presenter->flashMessage('Uzivatel smazan');
        $this->presenter->redirect('this');
    }


    /**
     * Render grid
     */
    public function render()
    {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->data = $this->userModel->getEntityTable()->order("id DESC")->fetchAll();
        $this->template->render();
    }
}
