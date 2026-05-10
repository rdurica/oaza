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

    /**
     * @return array{
     *     canMerge: bool,
     *     existingReservations: list<array{name: string, email: ?string, count: int}>,
     *     reservedCapacity: int,
     *     freeCapacity: int,
     * }|null
     */
    public function getCollisionData(int $reservationId, DateTime $newDate, int $newCount): ?array
    {
        $existing = $this->reservationManager->findReservationsByDateExcluding($newDate, $reservationId);
        if ($existing === []) {
            return null;
        }

        $reservedCapacity = 0;
        $mapped = [];
        foreach ($existing as $row) {
            $count = (int) $row->count;
            $reservedCapacity += $count;
            $name = $row->user_id !== null && $row->user !== null
                ? (string) $row->user->name
                : (string) $row->name;
            $mapped[] = [
                'name' => $name,
                'email' => $row->email !== null ? (string) $row->email : null,
                'count' => $count,
            ];
        }

        $freeCapacity = self::MAX_SLOT_CAPACITY - $reservedCapacity;

        return [
            'canMerge' => ($reservedCapacity + $newCount) <= self::MAX_SLOT_CAPACITY,
            'existingReservations' => $mapped,
            'reservedCapacity' => $reservedCapacity,
            'freeCapacity' => $freeCapacity,
        ];
    }

    /**
     * @throws CapacityExceededException
     * @throws NotAllowedOperationException
     * @throws ReservationInPastException
     */
    public function updateByAdmin(
        int $reservationId,
        ?DateTime $newDate,
        ?int $newCount,
        ?bool $hasChildren = null,
        ?string $confirmAction = null,
    ): void {
        $reservation = $this->requireReservation($reservationId);

        $oldDate = DateTime::from($reservation->date);
        $oldCount = (int) $reservation->count;

        $dateChanged = $newDate !== null && $newDate != $oldDate;
        $countChanged = $newCount !== null && $newCount !== $oldCount;
        $childrenChanged = $hasChildren !== null && $hasChildren !== (bool) $reservation->has_children;

        if ($dateChanged) {
            if ($newDate <= new DateTime()) {
                throw new ReservationInPastException();
            }

            $dayOfWeek = (int) $newDate->format('N');
            if ($dayOfWeek >= 6) {
                throw new NotAllowedOperationException();
            }

            $hour = (int) $newDate->format('H');
            if (!in_array($hour, ReservationCalendarService::getSlotHours(), true)) {
                throw new NotAllowedOperationException();
            }

            $minute = (int) $newDate->format('i');
            if ($minute !== 0) {
                throw new NotAllowedOperationException();
            }

            $countToCheck = $countChanged ? $newCount : $oldCount;
            $reservedCapacity = $this->reservationManager->getReservedCapacityByDateExcluding($newDate, $reservationId);
            if (($reservedCapacity + $countToCheck) > self::MAX_SLOT_CAPACITY) {
                if ($confirmAction === 'overwrite') {
                    $toCancel = $this->reservationManager->findReservationsByDateExcluding($newDate, $reservationId);
                    foreach ($toCancel as $existing) {
                        if ($reservedCapacity + $countToCheck <= self::MAX_SLOT_CAPACITY) {
                            break;
                        }
                        $this->cancelReservation($existing);
                        $reservedCapacity -= (int) $existing->count;
                    }
                } else {
                    throw new CapacityExceededException();
                }
            }
        }

        if ($countChanged && !$dateChanged && $newCount > $oldCount) {
            $reservedCapacity = $this->reservationManager->getReservedCapacityByDateExcluding($oldDate, $reservationId);
            if (($reservedCapacity + $newCount) > self::MAX_SLOT_CAPACITY) {
                if ($confirmAction === 'overwrite') {
                    $toCancel = $this->reservationManager->findReservationsByDateExcluding($oldDate, $reservationId);
                    foreach ($toCancel as $existing) {
                        if ($reservedCapacity + $newCount <= self::MAX_SLOT_CAPACITY) {
                            break;
                        }
                        $this->cancelReservation($existing);
                        $reservedCapacity -= (int) $existing->count;
                    }
                } else {
                    throw new CapacityExceededException();
                }
            }
        }

        $updateValues = [];
        if ($dateChanged) {
            $updateValues['date'] = $newDate;
        }
        if ($countChanged) {
            $updateValues['count'] = $newCount;
        }
        if ($childrenChanged) {
            $updateValues['has_children'] = $hasChildren ? 1 : 0;
        }

        if ($updateValues !== []) {
            $this->reservationManager->update($reservationId, $updateValues);
        }

        $emailAddress = $this->reservationManager->resolveNotificationEmail($reservation);
        $name = $reservation->user_id !== null && $reservation->user !== null
            ? (string) $reservation->user->name
            : (string) $reservation->name;

        if ($emailAddress !== null) {
            if ($dateChanged) {
                $this->mailService->sendReservationDateChanged(
                    $emailAddress,
                    $name,
                    $oldDate->format('j.n.Y H:i'),
                    $newDate->format('j.n.Y H:i'),
                    $oldCount,
                );
            } elseif ($countChanged) {
                $this->mailService->sendReservationCountChanged(
                    $emailAddress,
                    $name,
                    $oldDate->format('j.n.Y H:i'),
                    $oldCount,
                    $newCount,
                );
            } elseif ($childrenChanged) {
                $this->mailService->sendReservationChildrenChanged(
                    $emailAddress,
                    $name,
                    $oldDate->format('j.n.Y H:i'),
                    $oldCount,
                    $hasChildren ? 'Ano' : 'Ne',
                );
            }
        }
    }

    public function cancelReservation(ActiveRow $reservation): void
    {
        $emailAddress = $this->reservationManager->resolveNotificationEmail($reservation);
        $name = $reservation->user_id !== null && $reservation->user !== null
            ? (string) $reservation->user->name
            : (string) $reservation->name;

        $this->reservationManager->deleteById((int) $reservation->id);

        if ($emailAddress !== null) {
            $this->mailService->sendReservationCancelledByAdmin(
                $emailAddress,
                $name,
                DateTime::from($reservation->date)->format('j.n.Y H:i'),
                (int) $reservation->count,
            );
        }
    }
}
