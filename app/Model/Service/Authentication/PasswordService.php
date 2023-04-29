<?php

declare(strict_types=1);

namespace App\Model\Service\Authentication;

use App\Model\Manager\UserManager;
use App\Model\Service\Mail\MailService;

class PasswordService
{
    public function __construct(
        private readonly MailService $mailService,
        private readonly UserManager $userManager,
        private readonly Authenticator $authenticator,
    ) {
    }


    /**
     * Reset password
     * @throws \Exception
     */
    public function resetPassword(string $email): void
    {
        $account = $this->userManager->findByEmail($email)->fetch();

        if (!$account) {
            return; // account not found
        }

        $plainPassword = bin2hex(\random_bytes(16));
        $this->authenticator->changePassword($account->id, $plainPassword, true);
        $this->mailService->sendNewPassword($email, $plainPassword);
    }
}
