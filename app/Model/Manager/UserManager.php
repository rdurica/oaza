<?php

declare(strict_types=1);

namespace App\Model\Manager;

use App\Model\Manager;
use Nette\Database\Table\Selection;

/**
 * UserManager.
 *
 * @package   App\Model\Manager
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
final class UserManager extends Manager
{
    /** @inheritDoc */
    public function getEntityTable(): Selection
    {
        return $this->database->table("user");
    }

    /**
     * Find user by email.
     *
     * @param string $email
     * @return Selection
     */
    public function findByEmail(string $email): Selection
    {
        return $this->getEntityTable()->where(["email" => $email]);
    }

    /**
     * Set new password for user. If is temporary password user must change it after log-in.
     *
     * @param int    $userId
     * @param string $hash
     * @param bool   $isTempPassword
     * @return void
     */
    public function setPassword(int $userId, string $hash, bool $isTempPassword): void
    {
        $this->getEntityTable()->where("id = ?", $userId)->update([
            "password" => $hash,
            "password_resset" => $isTempPassword,
        ]);
    }

    /**
     * Delete user by id
     *
     * @param int $userId
     * @return void
     */
    public function delete(int $userId): void
    {
        $this->getEntityTable()->where('id = ?', $userId)->delete();
    }


    /**
     * Change user status to enabled/disabled
     *
     * @param int $userId
     * @return void
     */
    public function changeStatus(int $userId): void
    {
        $userData = $this->getEntityTable()->where('id = ?', $userId)->fetch();
        $isEnabled = ($userData->enabled === 0) ? 1 : 0;

        $userData->update([
            "enabled" => $isEnabled,
        ]);
    }
}
