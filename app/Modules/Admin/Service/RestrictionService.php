<?php

namespace App\Modules\Admin\Service;

use App\Model\Manager\ReservationManager;
use App\Modules\Admin\Manager\NewsManager;
use App\Modules\Admin\Manager\RestrictionManager;
use Nette\Database\Connection;
use Nette\Utils\DateTime;

/**
 * RestrictionService
 *
 * @package   App\Modules\Admin\Service
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */

class RestrictionService
{
    /**
     * Constructor.
     *
     * @param RestrictionManager $restrictionManager
     * @param NewsManager        $newsManager
     * @param ReservationManager $reservationManager
     * @param Connection         $database
     */
    public function __construct(
        private readonly RestrictionManager $restrictionManager,
        private readonly NewsManager $newsManager,
        private readonly ReservationManager $reservationManager,
        private readonly Connection $database,
    ) {
    }

    /**
     * Create restriction, news, block reservation slots and cancel reservation if needed.
     *
     * @param DateTime    $from
     * @param DateTime    $to
     * @param bool        $createNews
     * @param bool        $onHomepage
     * @param string|null $text
     * @return void
     * @throws \Exception
     */
    public function createRestriction(
        DateTime $from,
        DateTime $to,
        bool $createNews,
        bool $onHomepage,
        string $text = null
    ): void {
        try {
            $this->database->beginTransaction();

            // Create news
            $news = $createNews ? $this->newsManager->save($text, $onHomepage) : null;

            // Create restriction
            $this->restrictionManager->create($from, $to, $news?->id);

            // Create restricted slots
            $this->reservationManager->blockDays($from, $to);

            //Todo: Delete reservations and send emails

            $this->database->commit();
        } catch (\Exception $exception) {
            $this->database->rollBack();
            throw $exception;
        }
    }
}
