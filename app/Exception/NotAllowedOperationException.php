<?php

declare(strict_types=1);

namespace App\Exception;

class NotAllowedOperationException extends OazaException
{
    protected $message = "Operation is not allowed";
}