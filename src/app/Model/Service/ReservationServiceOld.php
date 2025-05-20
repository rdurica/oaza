<?php

declare(strict_types=1);

namespace App\Model\Service;

use App\Exception\CapacityExceededException;
use App\Exception\EmailNotSentException;
use App\Exception\NotAllowedOperationException;
use App\Exception\ReservationInPastException;
use App\Model\Manager\ReservationManager;
use App\Model\Service\Mail\MailService;
use Exception;
use JetBrains\PhpStorm\Deprecated;
use Nette\Security\User;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;

#[Deprecated("Will be replaced")]
class ReservationServiceOld
{
    public function __construct(
        private readonly ReservationManager $reservationManager,
        private readonly User $user,
        private readonly MailService $mailService
    )
    {
    }

    /**
     * @param ArrayHash $values
     *
     * @throws Exception
     */
    public function insert(ArrayHash $values): void
    {
        $count = $this->reservationManager->getReservationCountByDate($values->date);

        if ($values->date <= new DateTime())
        {
            throw new ReservationInPastException();
        }

        if (($values->count + $count) < 6)
        {
            $this->reservationManager->getEntityTable()->insert($values);

            try
            {
                $this->mailService->newReservation(
                    $values->email ?? $this->user->identity->email,
                    $values->name ?? $this->user->identity->name,
                    $values->date,
                    (bool)$values->has_children,
                    $values->count,
                    $values->comment
                );
            }
            catch (Exception $e)
            {
                throw new EmailNotSentException($e->getMessage(), $e->getCode(), $e);
            }
        } else
        {
            throw new CapacityExceededException();
        }
    }

    /**
     * Delete reservation
     *
     * @throws NotAllowedOperationException
     */
    public function delete(int $id, $admin = false): void
    {
        $reservationData = $this->reservationManager->getEntityTable()->where('id', $id)->fetch();

        if ($reservationData && ($reservationData['user_id'] === $this->user->id) || $admin === true)
        {
            $this->reservationManager->getEntityTable()->where('id', $id)->delete();
            $this->mailService->reservationCanceled($this->user->identity->email);
        } else
        {
            throw new NotAllowedOperationException();
        }
    }
}
