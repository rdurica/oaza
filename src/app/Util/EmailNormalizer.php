<?php

declare(strict_types=1);

namespace App\Util;

final class EmailNormalizer
{
    public static function normalize(string $email): string
    {
        return mb_strtolower(trim($email), 'UTF-8');
    }
}