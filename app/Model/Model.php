<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

abstract class Model
{
    public function __construct(protected Explorer $database)
    {
    }


    /**
     * Get model table
     */
    abstract public function getEntityTable(): Selection;
}
