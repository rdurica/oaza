<?php

declare(strict_types=1);

namespace App\Dto;

use App\Util\EmailNormalizer;
use Nette\Utils\DateTime;

/**
 * Reservation payload passed from UI to service layer.
 */
final class CreateReservationData
{
    public readonly ?string $email;

    public function __construct(
        public readonly DateTime $date,
        public readonly int $count,
        public readonly bool $hasChildren,
        public readonly string $comment = '',
        public readonly ?int $userId = null,
        ?string $email = null,
        public readonly ?string $name = null,
        public readonly ?string $telephone = null,
    ) {
        $this->email = $email !== null ? EmailNormalizer::normalize($email) : null;
    }
}
