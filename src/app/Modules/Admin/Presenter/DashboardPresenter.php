<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenter;

use App\Model\Manager\NewsManager;
use App\Model\Manager\ReservationManager;
use App\Model\Manager\UserManager;
use Nette\DI\Attributes\Inject;
use Nette\Utils\Json;

/**
 * DashboardPresenter
 *
 * @package   App\Modules\Admin\Presenter
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class DashboardPresenter extends AdminPresenter
{
    #[Inject]
    public ReservationManager $reservationManager;

    #[Inject]
    public UserManager $userManager;

    #[Inject]
    public NewsManager $newsManager;

    public function renderDefault(): void
    {
        $this->template->stats = [
            'totalUsers'          => $this->userManager->countTotal(),
            'enabledUsers'        => $this->userManager->countEnabled(),
            'activeReservations'  => $this->reservationManager->countActive(),
            'todaySeats'          => $this->reservationManager->sumTodaySeats(),
            'thisMonthCount'      => $this->reservationManager->countThisMonth(),
            'totalReservations'   => $this->reservationManager->countTotal(),
            'totalNews'           => $this->newsManager->countTotal(),
        ];
        $this->template->monthlyData = Json::encode($this->reservationManager->getMonthlyStats(6));
        $this->template->upcomingReservations = $this->reservationManager->findUpcoming(8);
    }
}
