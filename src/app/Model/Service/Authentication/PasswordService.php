<?php

declare(strict_types=1);

namespace App\Model\Service\Authentication;

use App\Model\Manager\UserManager;
use App\Model\Service\Mail\MailService;
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
     * @param MailService   $mailService
     * @param UserManager   $userManager
     * @param Authenticator $authenticator
     */
    public function __construct(
        private readonly MailService $mailService,
        private readonly UserManager $userManager,
        private readonly Authenticator $authenticator,
    ) {
    }


    /**
     * Create new temporary password and send it to user.
     *
     * @param string $email
     * @return void
     * @throws Exception
     */
    public function resetPassword(string $email): void
    {
        $account = $this->userManager->findByEmail($email)->fetch();

        if (!$account) {
            return; // account not found
        }

        $plainPassword = bin2hex(random_bytes(16));
        $this->authenticator->changePassword($account->id, $plainPassword, true);
        $this->mailService->sendTemporaryPassword($email, $plainPassword);
    }
}
