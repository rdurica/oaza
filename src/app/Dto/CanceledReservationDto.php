<?php declare(strict_types=1);

namespace App\Dto;

use Nette\Utils\DateTime;

/**
 * CanceledReservationDto.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-20
 */
final class CanceledReservationDto
{
    public string $email;

    public string $name;

    public DateTime $date;

    public int $count;
}
