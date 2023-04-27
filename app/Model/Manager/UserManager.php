<?php

declare(strict_types=1);

namespace App\Model\Manager;

use App\Model\Model;
use Nette\Database\Table\Selection;

final class UserManager extends Model
{
    public function getEntityTable(): Selection
    {
        return $this->database->table("user");
    }

    public function findByEmail(string $email): Selection
    {
        return $this->getEntityTable()->where(["email" => $email]);
    }

    public function setPassword(int $userId, string $hash): void
    {
        $this->getEntityTable()->where("id = ?", $userId)->update([
            "password" => $hash,
            "password_resset" => 0,
        ]);
    }

    /**
     * Delete User by id
     */
    public function deleteById($userId): void
    {
        $this->getEntityTable()->where('id = ?', $userId)->delete();
    }


    /**
     * Change enabled/disabled
     */
    public function changeStatus(int $userId): void
    {
        $userData = $this->getEntityTable()->where('id = ?', $userId);
        $isEnabled = ($userData->enabled === 0) ? 1 : 0;

        $userData->update([
            "enabled" => $isEnabled,
        ]);
    }
}
