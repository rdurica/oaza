<?php

declare(strict_types=1);

namespace Tests\Unit\Model\Service;

use App\Model\Service\ReservationCalendarService;
use PHPUnit\Framework\TestCase;

final class ReservationCalendarServiceTest extends TestCase
{
    public function testGetSlotHoursReturnsWeekdayMorningAndAfternoonSlots(): void
    {
        self::assertSame([8, 9, 10, 11, 13, 14, 15, 16, 17], ReservationCalendarService::getSlotHours());
    }
}
