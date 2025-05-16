<?php declare(strict_types=1);

namespace App\Exception;

/**
 * Account is blocked.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
class UserBlockedException extends OazaException
{
    /**
     * Constructor.
     *
     * @param string $email
     */
    public function __construct(string $email)
    {
        $message = $email . ' is blocked by administrator';

        parent::__construct($message);
    }
}
