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
        return $this->database->table('user');
    }

    /**
     * Find user by email.
     *
     * @param string $email
     * @return Selection
     */
    public function findByEmail(string $email): Selection
    {
        return $this->getEntityTable()->where(['email' => $email]);
    }

    /**
     * Set new password for user.
     *
     * @param int    $userId
     * @param string $hash
     * @return void
     */
    public function setPassword(int $userId, string $hash): void
    {
        $this->getEntityTable()->where('id = ?', $userId)->update([
            'password' => $hash,
        ]);
    }


    public function countTotal(): int
    {
        return (int) $this->getEntityTable()->count('*');
    }

    public function countEnabled(): int
    {
        return (int) $this->getEntityTable()
            ->where('enabled = 1')
            ->count('*');
    }

    /**
     * Find user by id.
     *
     * @param int $id
     * @return \Nette\Database\Table\ActiveRow|null
     */
    public function getById(int $id): ?\Nette\Database\Table\ActiveRow
    {
        return $this->getEntityTable()->where('id = ?', $id)->fetch();
    }

    /**
     * Update user.
     *
     * @param int   $id
     * @param array $data
     * @return void
     */
    public function updateUser(int $id, array $data): void
    {
        $this->getEntityTable()->where('id = ?', $id)->update($data);
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
            'enabled' => $isEnabled,
        ]);
    }
}
