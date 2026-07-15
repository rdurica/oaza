<?php

declare(strict_types=1);

namespace Tests\Unit\Util;

use App\Util\EmailNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EmailNormalizerTest extends TestCase
{
    #[DataProvider('provideEmails')]
    public function testNormalize(string $input, string $expected): void
    {
        self::assertSame($expected, EmailNormalizer::normalize($input));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function provideEmails(): array
    {
        return [
            'trims whitespace' => ['  user@example.com  ', 'user@example.com'],
            'lowercases' => ['User@Example.COM', 'user@example.com'],
            'combined' => ["\tAdmin@Oaza.cz\n", 'admin@oaza.cz'],
        ];
    }
}
