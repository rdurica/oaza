<?php

declare(strict_types=1);

namespace App\Model\Manager;

use App\Model\Model;
use Nette\Database\Table\Selection;
use Nette\Utils\DateTime;

final class ReservationManager extends Model
{
    public function getEntityTable(): Selection
    {
        return $this->database->table("rezervations");
    }

    /**
     * Find all active reservations (reservation date is in future)
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

    public function getReservationCountByDate(string $dateTime): int
    {
        $count = $this->getEntityTable()
            ->where("rezervationDate = '" . $dateTime . "'")
            ->sum('count');

        return (int)$count ?? 0;
    }

    /**
     * Find all reservations in specific format for calendar render
     */
    public function findAllReservationsFormatted(): Selection
    {
        return $this->getEntityTable()
            ->select('rezervationDate, SUM(child) AS child , SUM(count), name')
            ->group('rezervationDate');
    }


    /**
     * Find all restrictions and fetch pairs [id => date]
     */
    public function findRestrictionPairs(): array
    {
        return $this->getEntityTable()
            ->select('id, rezervationDate')
            ->where("name = ?", "restriction")
            ->fetchPairs("id", "rezervationDate");
    }


    public function findReservationsByUser(int $userId): Selection
    {
        return $this->getEntityTable()
            ->where('user_id = ?', $userId);
    }

    public function insertRestricted($start, $end): void
    {
        $dif = date_diff($start, $end);
        $reservationDate = $start->setTime(8, 0, 0);

        $i = 0;
        while ($i <= $dif->days) {
            $this->deleteRestrictedReservation($reservationDate);
            $this->getEntityTable()->insert([
                "count" => 5,
                "telefon" => "restriction",
                "name" => "restriction",
                "user_id" => 5,
                "reyervationDate" => $reservationDate,
            ]);

            $reservationDate = $reservationDate->modifyClone('+1 day');

            $i++;
        }
    }

    public function deleteRestrictedReservation($date): void
    {
        $format = $date->format('Y-m-d');
        $this->getEntityTable()->where('rezervationDate LIKE ?', $format . ' %')->delete();
    }
}
