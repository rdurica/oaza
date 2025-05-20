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

        $dto->from = new DateTime($data['from']);
        $dto->to = new DateTime($data['to']);
        $dto->showNewsOnHomepage = (bool)$data['showNewsOnHomepage'];
        $dto->message = (string)$data['message'];

        return $dto;
    }
}
