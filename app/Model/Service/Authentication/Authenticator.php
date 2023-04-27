<?php

/** @noinspection PhpUndefinedFieldInspection */

declare(strict_types=1);

namespace App\Model\Service\Authentication;

use App\Exception\UserBlockedException;
use App\Model\Manager\UserManager;
use Nette\Database\UniqueConstraintViolationException;
use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator as NetteAuthenticator;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;

readonly class Authenticator implements NetteAuthenticator
{
    public function __construct(private UserManager $userManager, private Passwords $passwords)
    {
    }


    /**
     * Performs an authentication.
     * @throws AuthenticationException
     * @throws UserBlockedException
     */
    public function authenticate(string $user, string $password): SimpleIdentity
    {
        $userEntity = $this->userManager->findByEmail($user)->fetch();
        if (!$userEntity) {
            throw new AuthenticationException("User not found");
        }

        if (!$this->passwords->verify($password, $userEntity->password)) {
            throw new AuthenticationException("Invalid credentials");
        }

        if ($userEntity->enabled === 0) {
            throw new UserBlockedException($userEntity->email);
        }

        if ($this->passwords->needsRehash($userEntity->password)) {
            $userEntity->update(["password" => $this->passwords->hash($password),]);
        }

        return new SimpleIdentity($userEntity->id, [$userEntity->role], [
            "email" => $userEntity->email,
            "name" => $userEntity->name,
            "telephone" => $userEntity->telephone,
            "needNewPassword" => (bool)$userEntity->password_resset,
        ]);
    }


    /**
     * Add new user
     * @throws UniqueConstraintViolationException
     */
    public function createAccount(
        string $email,
        #[\SensitiveParameter] string $plainPassword,
        string $name,
        int $telephone
    ) {
        $this->userManager->getEntityTable()->insert([
            "email" => $email,
            "name" => $name,
            "password" => $this->passwords->hash($plainPassword),
            "telephone" => $telephone
        ]);
    }
//
//
//
//    /**
//     * Resset password
//     * @param $email
//     * @param $password
//     * @throws \Exception
//     */
//    public function ressetByEmail($email, $password)
//    {
//        $values = new Nette\Utils\ArrayHash();
//        $values->password = $this->passwords->hash($password);
//        $values->password_resset = 1;
//
//        try {
//            $this->database->table(self::TABLE_NAME)
//                ->where('email', $email)
//                ->update($values);
//        } catch (\Exception $ex) {
//            throw new \Exception($ex->getMessage());
//        }
//    }
//
//
    /**
     * Change password
     */
    public function changePassword(int $id, #[\SensitiveParameter] string $password): void
    {
        $this->userManager->setPassword($id, $this->passwords->hash($password));

    }

}
