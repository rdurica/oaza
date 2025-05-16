<?php

declare(strict_types=1);

namespace App\Model\Service\Authentication;

use App\Exception\UserBlockedException;
use App\Model\Manager\UserManager;
use Nette\Database\UniqueConstraintViolationException;
use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator as NetteAuthenticator;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;
use SensitiveParameter;

/**
 * Handle authentication and account & password management.
 *
 * @package   App\Model\Service\Authentication
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class Authenticator implements NetteAuthenticator
{
    /**
     * Constructor.
     *
     * @param UserManager $userManager
     * @param Passwords   $passwords
     */
    public function __construct(
        private readonly UserManager $userManager,
        private readonly Passwords $passwords
    ) {
    }


    /**
     * Authenticate user.
     *
     * @param string $user
     * @param string $password
     * @return SimpleIdentity
     * @throws AuthenticationException
     * @throws UserBlockedException
     */
    public function authenticate(string $user, string $password): SimpleIdentity
    {
        $userEntity = $this->userManager->findByEmail($user)->fetch();
        if (!$userEntity) {
            throw new AuthenticationException('User not found');
        }

        if (!$this->passwords->verify($password, $userEntity->password)) {
            throw new AuthenticationException('Invalid credentials');
        }

        if ($userEntity->enabled === 0) {
            throw new UserBlockedException($userEntity->email);
        }

        if ($this->passwords->needsRehash($userEntity->password)) {
            $userEntity->update(['password' => $this->passwords->hash($password),]);
        }

        return new SimpleIdentity($userEntity->id, [$userEntity->role], [
            'email'           => $userEntity->email,
            'name'            => $userEntity->name,
            'telephone'       => $userEntity->telephone,
            'needNewPassword' => (bool)$userEntity->password_resset,
        ]);
    }


    /**
     * Create new account.
     *
     * @param string $email
     * @param string $plainPassword
     * @param string $name
     * @param int    $telephone
     * @return void
     */
    public function createAccount(
        string $email,
        #[SensitiveParameter] string $plainPassword,
        string $name,
        int $telephone
    ): void {
        $this->userManager->getEntityTable()->insert([
            'email'     => $email,
            'name'      => $name,
            'password'  => $this->passwords->hash($plainPassword),
            'telephone' => $telephone
        ]);
    }

    /**
     * Change user password.
     *
     * @param int    $id
     * @param string $password
     * @param bool   $isTempPassword
     *
     * @return void
     */
    public function changePassword(int $id, #[SensitiveParameter] string $password, bool $isTempPassword = false): void
    {
        $this->userManager->setPassword($id, $this->passwords->hash($password), $isTempPassword);
    }
}
