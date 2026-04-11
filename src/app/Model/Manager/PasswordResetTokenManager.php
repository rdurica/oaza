<?php

declare(strict_types=1);

namespace App\Model\Manager;

use App\Model\Manager;
use DateTimeImmutable;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

/**
 * PasswordResetTokenManager.
 *
 * @package   App\Model\Manager
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2026, Robert Durica
 */
final class PasswordResetTokenManager extends Manager
{
    private const TOKEN_EXPIRATION_HOURS = 3;

    /** @inheritDoc */
    public function getEntityTable(): Selection
    {
        return $this->database->table('password_reset_token');
    }

    /**
     * Create new password reset token.
     *
     * @param int $userId
     * @param string $token
     * @return void
     */
    public function createToken(int $userId, string $token): void
    {
        $expiresAt = new DateTimeImmutable('+' . self::TOKEN_EXPIRATION_HOURS . ' hours');

        $this->getEntityTable()->insert([
            'user_id'    => $userId,
            'token'      => $token,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Find valid token by token string.
     *
     * @param string $token
     * @return ActiveRow|false
     */
    public function findValidToken(string $token): ActiveRow|false
    {
        return $this->getEntityTable()
            ->where('token = ?', $token)
            ->where('expires_at > ?', new DateTimeImmutable())
            ->fetch() ?: false;
    }

    /**
     * Find valid token by token id.
     *
     * @param int $tokenId
     * @return ActiveRow|false
     */
    public function findValidTokenById(int $tokenId): ActiveRow|false
    {
        return $this->getEntityTable()
            ->where('id = ?', $tokenId)
            ->where('expires_at > ?', new DateTimeImmutable())
            ->fetch() ?: false;
    }

    /**
     * Delete token (after use).
     *
     * @param int $tokenId
     * @return void
     */
    public function deleteToken(int $tokenId): void
    {
        $this->getEntityTable()->where('id = ?', $tokenId)->delete();
    }

    /**
     * Clean up expired tokens.
     *
     * @return void
     */
    public function cleanupExpired(): void
    {
        $this->getEntityTable()
            ->where('expires_at < ?', new DateTimeImmutable())
            ->delete();
    }

    /**
     * Invalidate active tokens for user (without deleting request history).
     *
     * @param int $userId
     * @return void
     */
    public function invalidateActiveTokensForUser(int $userId): void
    {
        $now = new DateTimeImmutable();

        $this->getEntityTable()
            ->where('user_id = ?', $userId)
            ->where('expires_at > ?', $now)
            ->update([
                'expires_at' => $now->format('Y-m-d H:i:s'),
            ]);
    }
}
