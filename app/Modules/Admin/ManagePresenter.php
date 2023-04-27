<?php

namespace App\AdminModule\Presenter;

use App\Component\Form\CreateNews\ICreateNews;
use App\Component\Form\Restriction\IRestrictionForm;
use App\Model\Manager\ReservationManager;
use App\Model\Manager\UserManager;
use App\Model\Service\CalendarService;
use App\Model\Service\ReservationService;
use App\Modules\Admin\AdminPresenter;
use Nette\Utils\DateTime;

/**
 * Class ManagePresenter
 * @package App\AdminModule\Presenters
 */
final class ManagePresenter extends AdminPresenter
{
    /** @var  ICreateNews @inject */
    public $createNews;

    /** @var  IRestrictionForm @inject */
    public $restrictionForm;
//
//    /** @var IUser @inject */
//    public $userGrid;
//
//    /** @var  IRestrictionsGrid @inject */
//    public $restrictionGrid;

    /** @var  ReservationService @inject */
    public $reservationService;

    /** @var  CalendarService @inject */
    public $calendarService;

    /** @var UserManager @inject */
    public $userModel;

    /** @var  ReservationManager @inject */
    public $reservationModel;


    /**
     * @return \Oaza\Forms\CreateNews
     */
    public function createComponentNews()
    {
        return $this->createNews->create();
    }


    /**
     * @return \Oaza\Forms\RestrictionForm
     */
    public function createComponentRestrictionForm()
    {
        return $this->restrictionForm->create();
    }
//
//    /**
//     * @return \Oaza\Grids\User
//     */
//    public function createComponentUserGrid()
//    {
//        return $this->userGrid->create();
//    }
//
//
//    /**
//     * @return \Oaza\Grids\RestrictionsGrid
//     */
//    public function createComponentRestrictionGrid()
//    {
//        return $this->restrictionGrid->create();
//    }


    public function renderDefault()
    {
        $today = new DateTime();


        $this->template->registeredUsers = $this->userModel->getEntityTable()->count('id');
        $this->template->today = $this->reservationModel->getEntityTable()
            ->where('rezervationDate LIKE ?', $today->format('Y-m-d') . '%')->count('id');
    }


    /**
     * Render Calendar
     */
    public function renderReservations()
    {
        $resault = $this->calendarService->getAdminData();
        $this->template->data = $resault;
    }

    /**
     * Handler for cancel reservation
     */
    public function handleCancelRezervation(string $reservationId)
    {
        $this->reservationService->delete((int)$reservationId, true);
        $this->presenter->flashMessage('Rezervace zrusena');
        $this->presenter->redirect('this');
    }
}
