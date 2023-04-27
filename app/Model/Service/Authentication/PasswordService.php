<?php

declare(strict_types=1);

namespace App\Model\Service\Authentication;

use App\Model\Service\Mail\MailService;

readonly class PasswordService
{

    function __construct(private MailService $mailService)
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
