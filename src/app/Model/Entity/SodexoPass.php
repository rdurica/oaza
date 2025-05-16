<?php

declare(strict_types=1);

namespace App\Model\Entity;

/**
 * Class SodexoPass
 *
 * @package   App\Model\Entity
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class SodexoPass
{
    /** @var string Root path for sodexo pass images */
    private const BASE_PATH = "/assets/images/sodexo/";

    /** @var string Image extension */
    private const EXTENSION = ".jpg";

    /**
     * Constructor.
     *
     * @param string $name
     */
    private function __construct(private readonly string $name)
    {
    }

    /**
     * Factory method for object creation.
     *
     * @param string $name
     * @return self
     */
    public static function create(string $name): self
    {
        return new self($name);
    }

    /**
     * Get full path of image from app root dir.
     *
     * @return string
     */
    public function getFullPath(): string
    {
        return self::BASE_PATH . $this->name . self::EXTENSION;
    }

    /**
     * Get image name without extension.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
