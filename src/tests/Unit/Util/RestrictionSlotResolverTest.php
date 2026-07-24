<?php

declare(strict_types=1);

namespace Tests\Unit\Util;

use App\Util\RestrictionSlotResolver;
use Nette\Utils\DateTime;
use PHPUnit\Framework\TestCase;

final class RestrictionSlotResolverTest extends TestCase
{
    public function testDateOnlyRangeCollectsAllSlotsForEachDay(): void
    {
        $start = DateTime::from('2026-07-27 00:00:00');
        $end = DateTime::from('2026-07-28 00:00:00');

        $slots = RestrictionSlotResolver::collectSlots($start, $end);

        self::assertCount(18, $slots);
        self::assertSame('2026-07-27 08:00:00', $slots[0]->format('Y-m-d H:i:s'));
        self::assertSame('2026-07-27 17:00:00', $slots[8]->format('Y-m-d H:i:s'));
        self::assertSame('2026-07-28 08:00:00', $slots[9]->format('Y-m-d H:i:s'));
        self::assertSame('2026-07-28 17:00:00', $slots[17]->format('Y-m-d H:i:s'));
    }

    public function testPartDayRangeCollectsInclusiveHoursOnly(): void
    {
        $start = DateTime::from('2026-07-27 13:00:00');
        $end = DateTime::from('2026-07-27 15:00:00');

        $slots = RestrictionSlotResolver::collectSlots($start, $end);
        $formatted = array_map(
            static fn (DateTime $slot): string => $slot->format('Y-m-d H:i:s'),
            $slots,
        );

        self::assertSame([
            '2026-07-27 13:00:00',
            '2026-07-27 14:00:00',
            '2026-07-27 15:00:00',
        ], $formatted);
    }

    public function testUserReservationRangeForDateOnlyIsWholeDays(): void
    {
        [$from, $toExclusive] = RestrictionSlotResolver::resolveUserReservationRange(
            DateTime::from('2026-07-27 00:00:00'),
            DateTime::from('2026-07-28 00:00:00'),
        );

        self::assertSame('2026-07-27 00:00:00', $from->format('Y-m-d H:i:s'));
        self::assertSame('2026-07-29 00:00:00', $toExclusive->format('Y-m-d H:i:s'));
    }

    public function testUserReservationRangeForPartDayIsInclusiveSlots(): void
    {
        [$from, $toExclusive] = RestrictionSlotResolver::resolveUserReservationRange(
            DateTime::from('2026-07-27 13:00:00'),
            DateTime::from('2026-07-27 15:00:00'),
        );

        self::assertSame('2026-07-27 13:00:00', $from->format('Y-m-d H:i:s'));
        self::assertSame('2026-07-27 15:00:01', $toExclusive->format('Y-m-d H:i:s'));
    }
}
