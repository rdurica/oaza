<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Dto\CreateRestrictionDto;
use App\Model\Service\ReservationCalendarService;
use DateMalformedStringException;
use Nette\Utils\DateTime;

/**
 * CreateRestrictionDtoMapper.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-20
 */
final class CreateRestrictionDtoMapper
{
    public const string MODE_FULL_DAYS = 'fullDays';

    public const string MODE_PART_DAY = 'partDay';

    private const string PRIMARY_DATE_FORMAT = 'd.m.Y';

    private const string FALLBACK_DATE_FORMAT = 'Y-m-d';

    /**
     * Create dto from form data.
     *
     * @param array<string, mixed> $data
     *
     * @return CreateRestrictionDto
     * @throws DateMalformedStringException
     */
    public static function fromFormData(array $data): CreateRestrictionDto
    {
        $dto = new CreateRestrictionDto();
        $mode = (string) ($data['mode'] ?? self::MODE_FULL_DAYS);

        if ($mode === self::MODE_PART_DAY) {
            $date = self::parseDate((string) ($data['date'] ?? ''), 'date');
            $timeFrom = (int) ($data['timeFrom'] ?? 0);
            $timeTo = (int) ($data['timeTo'] ?? 0);
            self::assertValidSlotHour($timeFrom, 'timeFrom');
            self::assertValidSlotHour($timeTo, 'timeTo');

            $dto->from = DateTime::from($date)->setTime($timeFrom, 0, 0);
            $dto->to = DateTime::from($date)->setTime($timeTo, 0, 0);
        } else {
            $dto->from = self::parseDate((string) ($data['from'] ?? ''), 'from');
            $dto->to = self::parseDate((string) ($data['to'] ?? ''), 'to');
        }

        $dto->showNewsOnHomepage = (bool) ($data['showNewsOnHomepage'] ?? false);
        $dto->message = (string) ($data['message'] ?? '');

        return $dto;
    }

    /**
     * @throws DateMalformedStringException
     */
    private static function parseDate(string $value, string $field): DateTime
    {
        $value = trim($value);
        if ($value === '') {
            throw new DateMalformedStringException(sprintf('Date value for "%s" cannot be empty.', $field));
        }

        $date = DateTime::createFromFormat('!' . self::PRIMARY_DATE_FORMAT, $value);
        if ($date !== false && $date->format(self::PRIMARY_DATE_FORMAT) === $value) {
            return DateTime::from($date);
        }

        $isoDate = DateTime::createFromFormat('!' . self::FALLBACK_DATE_FORMAT, $value);
        if ($isoDate !== false && $isoDate->format(self::FALLBACK_DATE_FORMAT) === $value) {
            return DateTime::from($isoDate);
        }

        throw new DateMalformedStringException(
            sprintf(
                'Invalid date format for "%s": "%s". Supported formats: %s, %s.',
                $field,
                $value,
                self::PRIMARY_DATE_FORMAT,
                self::FALLBACK_DATE_FORMAT,
            )
        );
    }

    /**
     * @throws DateMalformedStringException
     */
    private static function assertValidSlotHour(int $hour, string $field): void
    {
        if (in_array($hour, ReservationCalendarService::getSlotHours(), true) === false) {
            throw new DateMalformedStringException(sprintf('Invalid slot hour for "%s": %d.', $field, $hour));
        }
    }
}
