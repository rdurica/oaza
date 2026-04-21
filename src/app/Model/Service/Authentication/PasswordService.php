<?php

declare(strict_types=1);

namespace App\Model\Service\Authentication;

use App\Model\Manager\PasswordResetRequestLogManager;
use App\Model\Manager\PasswordResetTokenManager;
use App\Model\Manager\UserManager;
use App\Model\Service\Mail\MailService;
use App\Util\EmailNormalizer;
use Exception;

/**
 * PasswordService.
 *
 * @package   App\Model\Service\Authentication
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class PasswordService
{
    /**
     * Constructor.
     *
     * @param MailService              $mailService
     * @param UserManager              $userManager
     * @param PasswordResetRequestLogManager $passwordResetRequestLogManager
     * @param PasswordResetTokenManager $passwordResetTokenManager
     */
    public function __construct(
        private readonly MailService $mailService,
        private readonly UserManager $userManager,
        private readonly PasswordResetRequestLogManager $passwordResetRequestLogManager,
        private readonly PasswordResetTokenManager $passwordResetTokenManager,
    ) {
    }


    /**
     * Create password reset token and send reset link to user.
     *
     * @param string $email
     * @return PasswordResetRequestResult
     * @throws Exception
     */
    public function resetPassword(string $email): PasswordResetRequestResult
    {
        $normalizedEmail = EmailNormalizer::normalize($email);
        $emailHash = hash('sha256', $normalizedEmail);

        $this->passwordResetRequestLogManager->cleanupOldRequests();

        if ($this->passwordResetRequestLogManager->isRateLimited($emailHash)) {
            return PasswordResetRequestResult::RATE_LIMITED;
        }

        $this->passwordResetRequestLogManager->createRequest($emailHash);

        $account = $this->userManager->findByEmail($normalizedEmail)->fetch();
        if (!$account) {
            return PasswordResetRequestResult::ACCEPTED;
        }

        $this->passwordResetTokenManager->cleanupExpired();
        $this->passwordResetTokenManager->invalidateActiveTokensForUser($account->id);

        $token = bin2hex(random_bytes(32));
        $this->passwordResetTokenManager->createToken($account->id, $token);
        $this->mailService->sendPasswordResetLink($account->email, $token);

        return PasswordResetRequestResult::ACCEPTED;
    }
}
