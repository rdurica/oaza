<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
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

    /**
     * Find one by id.
     *
     * @param int $id
     * @return ActiveRow|null
     */
    final public function find(int $id): ?ActiveRow
    {
        return $this->getEntityTable()->where('id = ?', $id)->fetch();
    }

    /**
     * Delete one by id.
     *
     * @param int $id
     * @return void
     */
    final public function delete(int $id): void
    {
        $this->getEntityTable()->where("id = ?", $id)->delete();
    }
}
