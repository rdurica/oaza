<?php

declare(strict_types=1);

namespace App\Model\Manager;

use App\Model\Model;
use Nette\Database\Table\Selection;
use Nette\Utils\DateTime;

final class RestrictionManager extends Model
{
    public function create(DateTime $from, DateTime $to, string $message): void
    {
        $this->getEntityTable()->insert([
            "from" => $from,
            "to" => $to,
            "message" => $message,
        ]);
    }

    public function getEntityTable(): Selection
    {
        return $this->database->table("restrictions");
    }

    public function findAllActive(): Selection
    {
        return $this->getEntityTable()->where("to >= ?", new \DateTime());
    }

    public function deleteById(int $id): void
    {
        $this->getEntityTable()->where('id', $id)->delete();
    }
}
