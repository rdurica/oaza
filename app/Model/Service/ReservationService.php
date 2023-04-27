<?php

declare(strict_types=1);

namespace App\Model\Service;

use App\Exception\NotAllowedOperationException;
use App\Model\Manager\ReservationManager;
use App\Model\Service\Mail\MailService;
use Nette\Security\User;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;

readonly class ReservationService
{


    public function __construct(
        private ReservationManager $reservationManager,
        private User $user,
        private MailService $mailService
    ) {
    }


    /**
     * @param ArrayHash $values
     * @throws \Exception
     */
    public function insert(ArrayHash $values): void
    {
        $count = $this->reservationManager->getReservationCountByDate($values->rezervationDate);

        if ($values->rezervationDate <= new DateTime()) {
            throw new \Exception('Termín rezervace musí být v budoucnu');
        }

        if (($values->count + $count) < 6) {
            $this->reservationManager->getEntityTable()->insert($values);

            $this->mailService->newReservation(
                $values->email ?? $this->user->identity->email,
                $values->name ?? $this->user->identity->name,
                $values->rezervationDate,
                $values->child,
                $values->count,
                $values->comment
            );
        } else {
            throw new \Exception('Překročena maximalni kapacita jeskyne');
        }
    }


    /**
     * Delete reservation
     * @throws NotAllowedOperationException
     */
    public function delete(int $id, $admin = false): void
    {
        $reservationData = $this->reservationManager->getEntityTable()->where('id', $id)->fetch();

        if ($reservationData && ($reservationData['user_id'] === $this->user->id) || $admin === true) {
            $this->reservationManager->getEntityTable()->where('id', $id)->delete();
            $this->mailService->reservationCanceled($this->user->identity->email);
        } else {
            throw new NotAllowedOperationException();
        }
    }
}
