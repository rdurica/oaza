<?php

declare(strict_types=1);

namespace App\Model\Manager;

use App\Dto\CanceledReservationDto;
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
        return $this->database->table('reservation');
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
               if(res.user_id, u.telephone, res.telephone) as telephone,
               if(res.has_children = 1, 'Ano', 'Ne') as hasChildren, 
               res.`count`,
               res.date as date
               from reservation as res
               left outer join user u on u.id = res.user_id
               where res.date >= ?
               order by res.id",
            new DateTime()
        )->fetchAll();
    }

    /**
     * Get number of reservations based on days. Returns only count of rows not sum.
     *
     * @param string $dateTime
     *
     * @return int
     */
    public function getReservationCountByDate(string $dateTime): int
    {
        $count = $this->getEntityTable()
            ->where("date = '" . $dateTime . "'")
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
            ->select('date, SUM(has_children) AS hasChildren , SUM(count), name')
            ->group('date');
    }

    /**
     * Find all reservations and fetch pairs [id => date].
     *
     * @return array
     */
    public function findReservationsPairs(): array
    {
        return $this->getEntityTable()
            ->select('id, date')
            ->where('name = ?', 'restriction')
            ->fetchPairs('id', 'date');
    }

    /**
     * Find all reservations for user.
     *
     * @param int $userId
     *
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
     *
     * @return void
     */
    public function restrictDaysForBooking(DateTime $start, DateTime $end): void
    {
        $dif = date_diff($start, $end);
        $date = $start->setTime(8, 0, 0);

        $i = 0;
        while ($i <= $dif->days)
        {
            $this->deleteRestrictedReservationByDate($date); // Do not block same day twice
            $this->getEntityTable()->insert([
                'count'     => 5,
                'telephone' => 'restriction',
                'name'      => 'restriction',
                'user_id'   => null,
                'date'      => $date,
            ]);

            $date = $date->modifyClone('+1 day');

            $i++;
        }
    }

    /**
     * Block days. New reservations will not be allowed.
     *
     * @param DateTime $start
     * @param DateTime $end
     *
     * @return CanceledReservationDto[]
     */
    public function cancelReservations(DateTime $start, DateTime $end): array
    {
        $dif = date_diff($start, $end);
        $date = $start->setTime(8, 0, 0);

        $result = [];
        $i = 0;
        while ($i <= $dif->days)
        {
            $this->deleteRestrictedReservationByDate($date);

            $userReservations = $this->findUserReservations($date);
            foreach ($userReservations as $reservation)
            {

                $registeredUser = $reservation->user_id !== null;

                $reservationCanceledDto = new CanceledReservationDto();
                $reservationCanceledDto->name = $registeredUser === true ? $reservation->user->name : $reservation->name;
                $reservationCanceledDto->email = $registeredUser === true ? $reservation->user->email : $reservation->email;
                $reservationCanceledDto->date = $reservation->date;

                $result[] = $reservationCanceledDto;

                $reservation->delete();
            }

            $date = $date->modifyClone('+1 day');

            $i++;
        }

        return $result;
    }

    /**
     * Delete restricted reservation by date.
     *
     * @param DateTime $date
     *
     * @return void
     */
    private function deleteRestrictedReservationByDate(DateTime $date): void
    {
        $format = $date->format('Y-m-d');
        $this->getEntityTable()
            ->where('date LIKE ?', $format . ' %')
            ->where('name = ?', 'restriction')
            ->delete();
    }

    /**
     * Delete restricted reservation by date.
     *
     * @param DateTime $date
     *
     * @return Selection
     */
    private function findUserReservations(DateTime $date): Selection
    {
        $reservationFrom = $date->setTime(0, 0, 0);
        $reservationTo = $reservationFrom->modifyClone('+1 day');

        return $this->getEntityTable()
            ->where('date >= ', $reservationFrom)
            ->where('date < ', $reservationTo)
            ->where('name != ? OR name IS NULL', 'restriction');
    }
}
