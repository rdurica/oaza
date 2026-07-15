<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\CreateReservationData;
use Nette\Utils\DateTime;
use PHPUnit\Framework\TestCase;

final class CreateReservationDataTest extends TestCase
{
    public function testEmailIsNormalizedWhenProvided(): void
    {
        $data = new CreateReservationData(
            date: DateTime::from('+1 week'),
            count: 2,
            hasChildren: false,
            email: '  Guest@Example.com ',
        );

        self::assertSame('guest@example.com', $data->email);
    }

    public function testEmailRemainsNullForLoggedInUser(): void
    {
        $data = new CreateReservationData(
            date: DateTime::from('+1 week'),
            count: 1,
            hasChildren: true,
            userId: 42,
        );

        self::assertNull($data->email);
    }
}
