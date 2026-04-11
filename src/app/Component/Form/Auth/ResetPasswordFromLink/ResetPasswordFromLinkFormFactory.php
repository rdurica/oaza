<?php declare(strict_types=1);

namespace App\Component\Form\Auth\ResetPasswordFromLink;

/**
 * Factory for reset password from link form.
 *
 * @copyright Copyright (c) 2026, Robert Durica
 * @since     2026-04-03
 */
interface ResetPasswordFromLinkFormFactory
{
    /**
     * Create form.
     *
     * @param int $userId
     * @param int $tokenId
     * @return ResetPasswordFromLink
     */
    public function create(int $userId, int $tokenId): ResetPasswordFromLink;
}
