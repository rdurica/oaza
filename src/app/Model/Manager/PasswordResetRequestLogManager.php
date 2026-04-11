<?php

declare(strict_types=1);

namespace App\Model\Manager;

use App\Model\Manager;
use DateTimeImmutable;
use Nette\Database\Table\Selection;

/**
 * PasswordResetRequestLogManager.
 *
 * @package   App\Model\Manager
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2026, Robert Durica
 */
final class PasswordResetRequestLogManager extends Manager
{
    private const RATE_LIMIT_WINDOW_HOURS = 1;

    private const MAX_REQUESTS_PER_HOUR = 3;

    /** @inheritDoc */
    public function getEntityTable(): Selection
    {
        return $this->database->table('password_reset_request_log');
    }

    /**
     * Store password reset request.
     *
     * @param string $emailHash
     * @return void
     */
    public function createRequest(string $emailHash): void
    {
        $this->getEntityTable()->insert([
            'email_hash' => $emailHash,
        ]);
    }

    /**
     * Check if email hash exceeded rate limit.
     *
     * @param string $emailHash
     * @return bool
     */
    public function isRateLimited(string $emailHash): bool
    {
        return $this->countRecentRequests($emailHash) >= self::MAX_REQUESTS_PER_HOUR;
    }

    /**
     * Cleanup old requests.
     *
     * @return void
     */
    public function cleanupOldRequests(): void
    {
        $windowStart = new DateTimeImmutable('-' . self::RATE_LIMIT_WINDOW_HOURS . ' hours');

        $this->getEntityTable()
            ->where('created_at < ?', $windowStart->format('Y-m-d H:i:s'))
            ->delete();
    }

    /**
     * Count recent password reset requests.
     *
     * @param string $emailHash
     * @return int
     */
    private function countRecentRequests(string $emailHash): int
    {
        $windowStart = new DateTimeImmutable('-' . self::RATE_LIMIT_WINDOW_HOURS . ' hours');

        return (int) $this->getEntityTable()
            ->where('email_hash = ?', $emailHash)
            ->where('created_at > ?', $windowStart->format('Y-m-d H:i:s'))
            ->count('*');
    }
}
