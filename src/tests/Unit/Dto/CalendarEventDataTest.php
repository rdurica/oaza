<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\CalendarEventData;
use PHPUnit\Framework\TestCase;

final class CalendarEventDataTest extends TestCase
{
    public function testToArrayIncludesRestrictionFlag(): void
    {
        $event = new CalendarEventData(
            title: 'Omezení provozu',
            start: '2026-06-01T09:00:00',
            end: '2026-06-01T19:00:00',
            isRestriction: true,
        );

        $array = $event->toArray();

        self::assertTrue($array['isRestriction']);
        self::assertSame('Omezení provozu', $array['title']);
    }
}
