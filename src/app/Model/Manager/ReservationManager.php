<?php

declare(strict_types=1);

namespace App\Model\Manager;

use App\Dto\CanceledReservationDto;
use App\Model\Manager;
use Nette\Database\Table\ActiveRow;
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
    public const string RESTRICTION_NAME = 'restriction';

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
               where res.date >= ? AND (res.telephone <> ? or res.telephone is null)
               order by res.date",
            new DateTime(),
            self::RESTRICTION_NAME,
        )->fetchAll();
    }

    /**
     * Get number of reservations based on days. Returns only count of rows not sum.
     *
     * @return int
     */
    public function getReservedCapacityByDate(DateTime $dateTime): int
    {
        $count = $this->getEntityTable()
            ->where('date = ?', $dateTime)
            ->sum('count');

        return (int) ($count ?? 0);
    }

    /**
     * Find all reservations in specific format for calendar render.
     *
     * @return list<ActiveRow>
     */
    public function findPublicCalendarSummaries(): array
    {
        return $this->getEntityTable()
            ->select('date, SUM(has_children) AS hasChildren, SUM(count) AS totalCount, name')
            ->group('date')
            ->fetchAll();
    }

    /**
     * Find all restricted days in YYYY-MM-DD format.
     *
     * @return list<string>
     */
    public function findRestrictedDates(): array
    {
        $dates = [];

        foreach (
            $this->getEntityTable()
                ->select('date')
                ->where('name = ?', self::RESTRICTION_NAME)
                ->fetchAll() as $restriction
        ) {
            $dates[] = DateTime::from($restriction->date)->format('Y-m-d');
        }

        return array_values(array_unique($dates));
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
     * @param array<string, mixed> $values
     */
    public function createReservation(array $values): void
    {
        $this->getEntityTable()->insert($values);
    }

    /** @var array<int, string> */
    private static array $czechMonths = [
        1 => 'Leden', 2 => 'Únor', 3 => 'Březen', 4 => 'Duben',
        5 => 'Květen', 6 => 'Červen', 7 => 'Červenec', 8 => 'Srpen',
        9 => 'Září', 10 => 'Říjen', 11 => 'Listopad', 12 => 'Prosinec',
    ];

    public function countActive(): int
    {
        return (int) $this->getEntityTable()
            ->where('date >= ?', new DateTime())
            ->where('telephone != ? OR telephone IS NULL', self::RESTRICTION_NAME)
            ->count('*');
    }

    public function sumTodaySeats(): int
    {
        $today = (new DateTime())->setTime(0, 0, 0);
        $tomorrow = (clone $today)->modify('+1 day');
        $count = $this->getEntityTable()
            ->where('date >= ?', $today)
            ->where('date < ?', $tomorrow)
            ->where('telephone != ? OR telephone IS NULL', self::RESTRICTION_NAME)
            ->sum('count');

        return (int) ($count ?? 0);
    }

    public function countThisMonth(): int
    {
        $start = new DateTime('first day of this month midnight');
        $end = new DateTime('first day of next month midnight');

        return (int) $this->getEntityTable()
            ->where('date >= ?', $start)
            ->where('date < ?', $end)
            ->where('telephone != ? OR telephone IS NULL', self::RESTRICTION_NAME)
            ->count('*');
    }

    public function countTotal(): int
    {
        return (int) $this->getEntityTable()
            ->where('telephone != ? OR telephone IS NULL', self::RESTRICTION_NAME)
            ->count('*');
    }

    /**
     * Get monthly reservation counts for the last N months.
     *
     * @return array<int, array{month: string, label: string, count: int, seats: int}>
     */
    public function getMonthlyStats(int $months = 6): array
    {
        $from = new DateTime('first day of this month midnight');
        $from->modify('-' . ($months - 1) . ' months');

        $rows = $this->database->query(
            "SELECT DATE_FORMAT(date, '%Y-%m') as month, COUNT(*) as count, SUM(`count`) as seats
             FROM reservation
             WHERE date >= ? AND (telephone != ? OR telephone IS NULL)
             GROUP BY DATE_FORMAT(date, '%Y-%m')
             ORDER BY month ASC",
            $from,
            self::RESTRICTION_NAME,
        )->fetchPairs('month');

        $stats = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = new DateTime('first day of this month midnight');
            $date->modify("-$i months");
            $key = $date->format('Y-m');
            $monthNum = (int) $date->format('n');
            $stats[] = [
                'month' => $key,
                'label' => self::$czechMonths[$monthNum] . ' ' . $date->format('Y'),
                'count' => isset($rows[$key]) ? (int) $rows[$key]->count : 0,
                'seats' => isset($rows[$key]) ? (int) $rows[$key]->seats : 0,
            ];
        }

        return $stats;
    }

    /**
     * Find next upcoming reservations ordered by date.
     *
     * @return array<int, mixed>
     */
    public function findUpcoming(int $limit = 8): array
    {
        return $this->database->query(
            "SELECT res.id,
                IF(res.user_id, u.name, res.name) as name,
                IF(res.user_id, u.email, res.email) as email,
                res.`count`,
                res.date as date,
                res.has_children
             FROM reservation as res
             LEFT OUTER JOIN user u ON u.id = res.user_id
             WHERE res.date >= ? AND (res.telephone != ? OR res.telephone IS NULL)
             ORDER BY res.date ASC
             LIMIT ?",
            new DateTime(),
            self::RESTRICTION_NAME,
            $limit,
        )->fetchAll();
    }

    public function findById(int $reservationId): ?ActiveRow
    {
        return $this->getEntityTable()
            ->where('id = ?', $reservationId)
            ->fetch();
    }

    public function deleteById(int $reservationId): void
    {
        $this->getEntityTable()
            ->where('id = ?', $reservationId)
            ->delete();
    }

    /**
     * Update reservation by id.
     *
     * @param int                   $reservationId
     * @param array<string, mixed>  $values
     *
     * @return void
     */
    public function update(int $reservationId, array $values): void
    {
        $this->getEntityTable()
            ->where('id = ?', $reservationId)
            ->update($values);
    }

    /**
     * Get reserved capacity by date excluding specific reservation.
     *
     * @param DateTime $dateTime
     * @param int      $excludeReservationId
     *
     * @return int
     */
    public function getReservedCapacityByDateExcluding(DateTime $dateTime, int $excludeReservationId): int
    {
        $count = $this->getEntityTable()
            ->where('date = ?', $dateTime)
            ->where('id != ?', $excludeReservationId)
            ->sum('count');

        return (int) ($count ?? 0);
    }

    /**
     * Find all user reservations on specific date excluding given reservation.
     *
     * @param DateTime $dateTime
     * @param int      $excludeReservationId
     *
     * @return array<ActiveRow>
     */
    public function findReservationsByDateExcluding(DateTime $dateTime, int $excludeReservationId): array
    {
        return $this->getEntityTable()
            ->where('date = ?', $dateTime)
            ->where('id != ?', $excludeReservationId)
            ->where('name != ? OR name IS NULL', self::RESTRICTION_NAME)
            ->order('id DESC')
            ->fetchAll();
    }

    public function resolveNotificationEmail(ActiveRow $reservation): ?string
    {
        if ($reservation->user_id !== null && $reservation->user !== null) {
            return (string) $reservation->user->email;
        }

        if ($reservation->email === null) {
            return null;
        }

        return (string) $reservation->email;
    }

    /**
     * Delete restricted days in range. Removes blockers only.
     *
     * @param DateTime $start
     * @param DateTime $end
     *
     * @return void
     */
    public function deleteRestrictedDaysForBooking(DateTime $start, DateTime $end): void
    {
        $dif = date_diff($start, $end);
        $date = $start->setTime(8, 0, 0);

        $i = 0;
        while ($i <= $dif->days) {
            $this->deleteRestrictedReservationByDate($date);
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
     * @return void
     */
    public function restrictDaysForBooking(DateTime $start, DateTime $end): void
    {
        $dif = date_diff($start, $end);
        $date = $start->setTime(8, 0, 0);

        $i = 0;
        while ($i <= $dif->days) {
            $this->deleteRestrictedReservationByDate($date); // Do not block same day twice
            $this->getEntityTable()->insert([
                'count'     => 5,
                'telephone' => self::RESTRICTION_NAME,
                'name'      => self::RESTRICTION_NAME,
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
        while ($i <= $dif->days) {
            $this->deleteRestrictedReservationByDate($date);

            $userReservations = $this->findUserReservations($date)->fetchAll();
            foreach ($userReservations as $reservation) {
                $registeredUser = $reservation->user_id !== null;

                $reservationCanceledDto = new CanceledReservationDto();
                $reservationCanceledDto->name = $registeredUser === true
                    ? $reservation->user->name
                    : $reservation->name;
                $reservationCanceledDto->email = $registeredUser === true
                    ? $reservation->user->email
                    : $reservation->email;
                $reservationCanceledDto->date = $reservation->date;
                $reservationCanceledDto->count = (int) $reservation->count;

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
            ->where('name = ?', self::RESTRICTION_NAME)
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
            ->where('name != ? OR name IS NULL', self::RESTRICTION_NAME);
    }
}
