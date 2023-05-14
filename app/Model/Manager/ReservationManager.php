<?php

declare(strict_types=1);

namespace App\Model\Manager;

use App\Model\Manager;
use Nette\Database\Table\Selection;
use Nette\Utils\DateTime;

/**
 * ReservationManager.
 *
 * @package   App\Model\Manager
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
final class ReservationManager extends Manager
{
    /** @inheritDoc */
    public function getEntityTable(): Selection
    {
        return $this->database->table("reservation");
    }

    /**
     * Find all active reservations (reservation date is in future).
     *
     * @return array
     */
    public function findAllActive(): array
    {
        //todo: Rewrite after db refactor
        return $this->database->query(
            "select res.id,
               if(res.user_id, u.name, res.name) as name,
               if(res.user_id, u.email, res.email) as email,
               if(res.user_id, u.telephone, res.telefon) as telephone,
               if(res.child = 1, 'Ano', 'Ne') as children, 
               res.`count`,
               res.rezervationDate as reservationDate
               from rezervations as res
               left outer join user u on u.id = res.user_id
               where res.rezervationDate >= ?
               order by res.id ASC",
            new DateTime()
        )->fetchAll();
    }

    /**
     * Get number of reservations based on days. Returns only count of rows not sum.
     *
     * @param string $dateTime
     * @return int
     */
    public function getReservationCountByDate(string $dateTime): int
    {
        $count = $this->getEntityTable()
            ->where("rezervationDate = '" . $dateTime . "'")
            ->sum('count');

        return (int)$count ?? 0;
    }

    /**
     * Find all reservations in specific format for calendar render.
     *
     * @return Selection
     */
    public function findAllReservationsFormatted(): Selection
    {
        return $this->getEntityTable()
            ->select('rezervationDate, SUM(child) AS child , SUM(count), name')
            ->group('rezervationDate');
    }


    /**
     * Find all reservations and fetch pairs [id => date].
     *
     * @return array
     */
    public function findReservationsPairs(): array
    {
        return $this->getEntityTable()
            ->select('id, rezervationDate')
            ->where("name = ?", "restriction")
            ->fetchPairs("id", "rezervationDate");
    }


    /**
     * Find all reservations for user.
     *
     * @param int $userId
     * @return Selection
     */
    public function findReservationsByUser(int $userId): Selection
    {
        return $this->getEntityTable()
            ->where('user_id = ?', $userId);
    }

    /**
     * Block days. New reservations will not be allowed.
     *
     * @param DateTime $start
     * @param DateTime $end
     * @return void
     */
    public function blockDays(DateTime $start, DateTime $end): void
    {
        $dif = date_diff($start, $end);
        $reservationDate = $start->setTime(8, 0, 0);

        $i = 0;
        while ($i <= $dif->days) {
            $this->deleteRestrictedReservationByDate($reservationDate); // Do not block same day twice
            $this->getEntityTable()->insert([
                "reservation_date" => $reservationDate,
                "quantity" => 5,
                "is_restricted" => true
            ]);

            $reservationDate = $reservationDate->modifyClone('+1 day');

            $i++;
        }
    }

    /**
     * Delete restricted reservation by date.
     *
     * @param DateTime $date
     * @return void
     */
    private function deleteRestrictedReservationByDate(DateTime $date): void
    {
        $format = $date->format('Y-m-d');
        $this->getEntityTable()
            ->where('reservation_date LIKE ?', $format . ' %')
            ->where("is_restricted = ?", true)
            ->delete();
    }
}
