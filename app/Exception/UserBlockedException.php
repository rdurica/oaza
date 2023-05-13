<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Occurs when user try to log-in but account is blocked.
 *
 * @package   App\Exception
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class UserBlockedException extends OazaException
{
    public function __construct(string $email)
    {
        $message = $email . " is blocked by administrator";
        parent::__construct($message);
    }
}
