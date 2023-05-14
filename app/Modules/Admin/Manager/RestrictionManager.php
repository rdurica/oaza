<?php

declare(strict_types=1);

namespace App\Modules\Admin\Manager;

use App\Model\Manager;
use Nette\Database\Table\ActiveRow;
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
        return $this->database->table("restriction");
    }

    /**
     * Create restriction.
     *
     * @param DateTime $from
     * @param DateTime $to
     * @param int|null $newsId
     * @return ActiveRow|null
     */
    public function create(DateTime $from, DateTime $to, int $newsId = null): ?ActiveRow
    {
        return $this->getEntityTable()->insert([
            "from" => $from,
            "to" => $to,
            "news_id" => $newsId,
        ]);
    }


    /**
     * Find all active restrictions.
     *
     * @return Selection
     */
    public function findAllActive(): Selection
    {
        return $this->getEntityTable()->where("to >= ?", new \DateTime());
    }
}
