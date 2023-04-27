<?php

declare(strict_types=1);

namespace App\Exception;

class UserBlockedException extends OazaException
{
    public function __construct(string $email)
    {
        $message  = $email . " is blocked by administrator";
        parent::__construct($message);
    }
}
