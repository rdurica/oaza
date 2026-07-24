<?php

declare(strict_types=1);

namespace Tests\Unit\Mapper;

use App\Mapper\CreateRestrictionDtoMapper;
use PHPUnit\Framework\TestCase;

final class CreateRestrictionDtoMapperTest extends TestCase
{
    public function testMapsFullDaysMode(): void
    {
        $dto = CreateRestrictionDtoMapper::fromFormData([
            'mode' => CreateRestrictionDtoMapper::MODE_FULL_DAYS,
            'from' => '27.07.2026',
            'to' => '29.07.2026',
            'message' => 'Údržba',
            'showNewsOnHomepage' => 1,
        ]);

        self::assertSame('2026-07-27 00:00:00', $dto->from->format('Y-m-d H:i:s'));
        self::assertSame('2026-07-29 00:00:00', $dto->to->format('Y-m-d H:i:s'));
        self::assertTrue($dto->showNewsOnHomepage);
        self::assertSame('Údržba', $dto->message);
    }

    public function testMapsPartDayMode(): void
    {
        $dto = CreateRestrictionDtoMapper::fromFormData([
            'mode' => CreateRestrictionDtoMapper::MODE_PART_DAY,
            'date' => '27.07.2026',
            'timeFrom' => 13,
            'timeTo' => 15,
            'message' => 'Pauza',
            'showNewsOnHomepage' => 0,
        ]);

        self::assertSame('2026-07-27 13:00:00', $dto->from->format('Y-m-d H:i:s'));
        self::assertSame('2026-07-27 15:00:00', $dto->to->format('Y-m-d H:i:s'));
        self::assertFalse($dto->showNewsOnHomepage);
        self::assertSame('Pauza', $dto->message);
    }
}
