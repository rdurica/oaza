<?php

declare(strict_types=1);

namespace App\Util;

use App\Model\Service\ReservationCalendarService;
use Nette\Utils\DateTime;

/**
 * Resolves bookable slots covered by a restriction interval.
 */
final class RestrictionSlotResolver
{
    public static function isDateOnlyRange(DateTime $start, DateTime $end): bool
    {
        return $start->format('H:i:s') === '00:00:00'
            && $end->format('H:i:s') === '00:00:00';
    }

    /**
     * @return list<DateTime>
     */
    public static function collectSlots(DateTime $start, DateTime $end): array
    {
        $hours = ReservationCalendarService::getSlotHours();
        $slots = [];

        $day = DateTime::from($start->format('Y-m-d') . ' 00:00:00');
        $lastDay = $end->format('Y-m-d');

        if (self::isDateOnlyRange($start, $end)) {
            while ($day->format('Y-m-d') <= $lastDay) {
                foreach ($hours as $hour) {
                    $slot = DateTime::from($day);
                    $slot->setTime($hour, 0, 0);
                    $slots[] = $slot;
                }
                $day = $day->modifyClone('+1 day');
            }

            return $slots;
        }

        $startTs = $start->getTimestamp();
        $endTs = $end->getTimestamp();

        while ($day->format('Y-m-d') <= $lastDay) {
            foreach ($hours as $hour) {
                $slot = DateTime::from($day);
                $slot->setTime($hour, 0, 0);
                $ts = $slot->getTimestamp();
                if ($ts >= $startTs && $ts <= $endTs) {
                    $slots[] = $slot;
                }
            }
            $day = $day->modifyClone('+1 day');
        }

        return $slots;
    }

    /**
     * @return array{0: DateTime, 1: DateTime} inclusive start, exclusive end
     */
    public static function resolveUserReservationRange(DateTime $start, DateTime $end): array
    {
        if (self::isDateOnlyRange($start, $end)) {
            $from = DateTime::from($start->format('Y-m-d') . ' 00:00:00');
            $toExclusive = DateTime::from($end->format('Y-m-d') . ' 00:00:00')->modifyClone('+1 day');

            return [$from, $toExclusive];
        }

        $from = DateTime::from($start);
        $toExclusive = DateTime::from($end)->modifyClone('+1 second');

        return [$from, $toExclusive];
    }
}
