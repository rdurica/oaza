<?php declare(strict_types=1);

namespace App\Dto;

use Nette\Utils\DateTime;

/**
 * CreateRestrictionDto.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-20
 */
final class CreateRestrictionDto
{
    public DateTime $from;

    public DateTime $to;

    public bool $showNewsOnHomepage;

    public string $message;
}
