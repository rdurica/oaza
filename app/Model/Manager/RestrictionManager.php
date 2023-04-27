<?php

declare(strict_types=1);

namespace App\Model\Manager;

use App\Model\Model;
use Nette\Database\Table\Selection;


final class RestrictionManager extends Model
{
    public function getEntityTable(): Selection
    {
        return $this->database->table("restrictions");
    }
}
