<?php declare(strict_types=1);

namespace App\Exception;

/**
 * Occurs when user call unauthorized request.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
class NotAllowedOperationException extends OazaException
{
    /**
     * Constructor,
     */
    public function __construct()
    {
        parent::__construct('Operation is not allowed');
    }
}
