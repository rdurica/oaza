<?php declare(strict_types=1);

namespace App\Mapper;

use App\Dto\CreateRestrictionDto;
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
    private const string PRIMARY_DATE_FORMAT = 'd.m.Y';
    private const string FALLBACK_DATE_FORMAT = 'Y-m-d';

    /**
     * Create dto from form data.
     *
     * @param array{from: string, to: string, message: string, showNewsOnHomepage: int} $data
     *
     * @return CreateRestrictionDto
     * @throws DateMalformedStringException
     */
    public static function fromFormData(array $data): CreateRestrictionDto
    {
        $dto = new CreateRestrictionDto();

        $dto->from = self::parseDate($data['from'], 'from');
        $dto->to = self::parseDate($data['to'], 'to');
        $dto->showNewsOnHomepage = (bool)$data['showNewsOnHomepage'];
        $dto->message = (string)$data['message'];

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
}
