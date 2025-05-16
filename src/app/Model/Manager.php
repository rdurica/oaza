<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

/**
 * Base manager. All managers should inherit from it.
 *
 * @package   App\Model
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
abstract class Manager
{
    /**
     * Constructor.
     *
     * @param Explorer $database
     */
    public function __construct(protected Explorer $database)
    {
    }

    /**
     * Get manager table.
     *
     * @return Selection
     */
    abstract public function getEntityTable(): Selection;
}
