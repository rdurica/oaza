<?php

declare(strict_types=1);

namespace App\Model\Service\Authentication;

/**
 * Result of reset password request processing.
 *
 * @copyright Copyright (c) 2026, Robert Durica
 */
enum PasswordResetRequestResult: string
{
    case ACCEPTED = 'accepted';
    case RATE_LIMITED = 'rate_limited';
}
