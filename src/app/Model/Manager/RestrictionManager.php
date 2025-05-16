<?php

declare(strict_types=1);

namespace App\Model\Manager;

use App\Model\Manager;
use Nette\Database\Table\Selection;
use Nette\Utils\DateTime;

/**
 * RestrictionManager.
 *
 * @package   App\Modules\Admin\Manager
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
final class RestrictionManager extends Manager
{
    /** @inheritDoc */
    public function getEntityTable(): Selection
    {
        return $this->database->table('restrictions');
    }

    /**
     * Create restriction.
     *
     * @param DateTime $from
     * @param DateTime $to
     * @param string   $message
     * @return void
     */
    public function create(DateTime $from, DateTime $to, string $message): void
    {
        $this->getEntityTable()->insert([
            'from'    => $from,
            'to'      => $to,
            'message' => $message,
        ]);
    }


    /**
     * Find all active restrictions.
     *
     * @return Selection
     */
    public function findAllActive(): Selection
    {
        return $this->getEntityTable()->where('to >= ?', new \DateTime());
    }

    /**
     * Delete restriction.
     *
     * @param int $id
     * @return void
     */
    public function delete(int $id): void
    {
        $this->getEntityTable()->where('id = ?', $id)->delete();
    }
}
