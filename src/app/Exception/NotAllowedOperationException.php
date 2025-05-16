<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Occurs when user call unauthorized request.
 *
 * @package   App\Exception
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class NotAllowedOperationException extends OazaException
{
    protected $message = "Operation is not allowed";
}
