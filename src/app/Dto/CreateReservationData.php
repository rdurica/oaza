<?php

declare(strict_types=1);

namespace App\Dto;

use Nette\Utils\DateTime;

/**
 * Reservation payload passed from UI to service layer.
 */
final class CreateReservationData
{
    public function __construct(
        public readonly DateTime $date,
        public readonly int $count,
        public readonly bool $hasChildren,
        public readonly string $comment = '',
        public readonly ?int $userId = null,
        public readonly ?string $email = null,
        public readonly ?string $name = null,
        public readonly ?string $telephone = null,
    ) {
    }
}
