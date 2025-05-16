<?php declare(strict_types=1);

namespace App\Util;

/**
 * Types of flash messages.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
class FlashType
{
    /** @var string Red. */
    public const ERROR = 'error';

    /** @var string Orange. */
    public const WARNING = 'warning';

    /** @var string Green. */
    public const SUCCESS = 'success';

    /** @var string Blue. */
    public const INFO = 'info';
}
