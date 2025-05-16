<?php

declare(strict_types=1);

namespace App\Util;

/**
 * All types of flash message
 *
 * @package   App\Util
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class FlashType
{
    public const ERROR = 'error';
    public const WARNING = 'warning';
    public const SUCCESS = 'success';
    public const INFO = 'info';
    public const QUESTION = 'question';
}
