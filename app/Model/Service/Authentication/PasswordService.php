<?php

declare(strict_types=1);

namespace App\Model\Service\Authentication;

use App\Model\Service\Mail\MailService;

class PasswordService
{
    public function __construct(private readonly MailService $mailService)
    {
    }


    /**
     * Reset password
     */
    public function resetPassword(string $email): void
    {
        // Todo: Generate url
        // send by email link
    }
}
