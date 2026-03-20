<?php

declare(strict_types=1);

namespace App\Model\Service;

use App\Dto\CreateReservationData;
use App\Exception\CapacityExceededException;
use App\Exception\EmailNotSentException;
use App\Exception\NotAllowedOperationException;
use App\Exception\ReservationInPastException;
use App\Model\Manager\ReservationManager;
use App\Model\Service\Mail\MailService;
use Exception;
use Nette\Database\Table\ActiveRow;
use Nette\Security\User;
use Nette\Utils\DateTime;

/**
 * Handles reservation mutations and related notifications.
 */
final class ReservationService
{
    private const int MAX_SLOT_CAPACITY = 5;

    public function __construct(
        private readonly ReservationManager $reservationManager,
        private readonly User $user,
        private readonly MailService $mailService,
    ) {
    }

    /**
     * @throws CapacityExceededException
     * @throws EmailNotSentException
     * @throws ReservationInPastException
     */
    public function create(CreateReservationData $reservationData): void
    {
        if ($reservationData->date <= new DateTime()) {
            throw new ReservationInPastException();
        }

        $reservedCapacity = $this->reservationManager->getReservedCapacityByDate($reservationData->date);
        if (($reservedCapacity + $reservationData->count) > self::MAX_SLOT_CAPACITY) {
            throw new CapacityExceededException();
        }

        $this->reservationManager->createReservation([
            'count' => $reservationData->count,
            'telephone' => $reservationData->telephone,
            'name' => $reservationData->name,
            'has_children' => $reservationData->hasChildren,
            'email' => $reservationData->email,
            'user_id' => $reservationData->userId,
            'date' => $reservationData->date,
            'comment' => $reservationData->comment,
        ]);

        try {
            $this->mailService->sendNewReservationDetails(
                $reservationData->email ?? (string) $this->user->getIdentity()?->email,
                $reservationData->name ?? (string) $this->user->getIdentity()?->name,
                $reservationData->date->format('Y-m-d H:i:s'),
                $reservationData->hasChildren,
                $reservationData->count,
                $reservationData->comment,
            );
        } catch (Exception $exception) {
            throw new EmailNotSentException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception,
            );
        }
    }

    /**
     * @throws NotAllowedOperationException
     */
    public function cancelByUser(int $reservationId): void
    {
        $reservation = $this->requireReservation($reservationId);

        if ($reservation->user_id !== $this->user->getId()) {
            throw new NotAllowedOperationException();
        }

        $this->cancelReservation($reservation);
    }

    public function cancelByAdmin(int $reservationId): void
    {
        $reservation = $this->requireReservation($reservationId);
        $this->cancelReservation($reservation);
    }

    /**
     * @throws NotAllowedOperationException
     */
    private function requireReservation(int $reservationId): ActiveRow
    {
        $reservation = $this->reservationManager->findById($reservationId);
        if ($reservation === null) {
            throw new NotAllowedOperationException();
        }

        return $reservation;
    }

    private function cancelReservation(ActiveRow $reservation): void
    {
        $emailAddress = $this->reservationManager->resolveNotificationEmail($reservation);

        $this->reservationManager->deleteById((int) $reservation->id);

        if ($emailAddress !== null) {
            $this->mailService->sendReservationCancellation($emailAddress);
        }
    }
}
