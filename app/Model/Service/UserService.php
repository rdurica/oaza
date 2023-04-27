<?php

declare(strict_types=1);

namespace App\Model\Service;

use App\Model\Manager\UserManager;
use Nette\Security\Passwords;

class UserService
{
    public function __construct(private UserManager $userManager, private Passwords $passwords)
    {
    }

    /**
     * Set new password for user
     */
    public function setPassword(int $userId, string $plainPassword): void
    {
        $hash = $this->passwords->hash($plainPassword);
        $this->userManager->setPassword($userId, $hash);
    }
}
