<?php

declare(strict_types=1);

namespace App\Model\Service;

use App\Dto\CalendarEventData;
use App\Model\Manager\ReservationManager;
use Nette\Utils\DateTime;

use function array_map;
use function array_merge;
use function array_unique;
use function count;
use function in_array;
use function range;

/**
 * Builds normalized event payloads for FullCalendar views.
 */
final class ReservationCalendarService
{
    private const int MAX_SLOT_CAPACITY = 5;

    private const int SLOT_DURATION_MINUTES = 45;

    private const int RESTRICTION_DURATION_MINUTES = 600;

    private const int LOOKAHEAD_DAYS = 100;

    public function __construct(private readonly ReservationManager $reservationManager)
    {
    }

    /**
     * @return array<int, array<string, bool|int|string|null>>
     */
    public function getPublicCalendarEvents(): array
    {
        $calendarEvents = [];
        $usedSlots = [];

        foreach ($this->reservationManager->findPublicCalendarSummaries() as $row) {
            $slotDate = DateTime::from($row->date);
            $usedSlots[] = $slotDate->format('Y-m-d H:i:s');

            $availableCapacity = self::MAX_SLOT_CAPACITY - (int) $row->totalCount;
            $isRestriction = $row->name === ReservationManager::RESTRICTION_NAME;
            $eventTime = $this->formatEventTime($slotDate, $isRestriction);
            $title = $isRestriction
                ? 'Omezeni provozu'
                : $this->createAvailabilityMessage($availableCapacity)
                . $this->getHasChildrenText((int) $row->hasChildren > 0);

            $calendarEvents[] = new CalendarEventData(
                title: $title,
                start: $eventTime['start'],
                end: $eventTime['end'],
                color: $this->resolvePublicEventColor($isRestriction, $availableCapacity),
                hasChildren: $this->getHasChildrenText((int) $row->hasChildren > 0),
            );
        }

        return array_map(
            static fn (CalendarEventData $event): array => $event->toArray(),
            array_merge($this->createEmptySlots($usedSlots), $calendarEvents),
        );
    }

    /**
     * @return array<int, array<string, bool|int|string|null>>
     */
    public function getUserCalendarEvents(int $userId): array
    {
        $events = [];

        foreach ($this->reservationManager->findReservationsByUser($userId)->fetchAll() as $reservation) {
            $slotDate = DateTime::from($reservation->date);
            $eventTime = $this->formatEventTime($slotDate);

            $events[] = new CalendarEventData(
                title: 'Rezervováno (' . (int) $reservation->count . ')',
                start: $eventTime['start'],
                end: $eventTime['end'],
                color: $this->resolveHistoricalColor($slotDate),
                hasChildren: $this->getHasChildrenText((bool) $reservation->has_children, 'Ne'),
                id: (int) $reservation->id,
                totalCount: (int) $reservation->count,
                date: $slotDate->format('H:i'),
                comment: (string) ($reservation->comment ?? ''),
            );
        }

        return array_map(
            static fn (CalendarEventData $event): array => $event->toArray(),
            $events,
        );
    }

    /**
     * @param list<string> $usedSlots
     * @return list<CalendarEventData>
     */
    private function createEmptySlots(array $usedSlots): array
    {
        $events = [];
        $restrictedDates = $this->reservationManager->findRestrictedDates();

        foreach (range(0, self::LOOKAHEAD_DAYS - 1) as $dayOffset) {
            $date = new DateTime('now +' . $dayOffset . 'days');
            $date->setTime(8, 0, 0);

            if (in_array($date->format('Y-m-d'), $restrictedDates, true)) {
                continue;
            }

            foreach ($this->getSlotHours() as $hour) {
                $date->setTime($hour, 0, 0);
                $slotKey = $date->format('Y-m-d H:i:s');

                if (in_array($slotKey, $usedSlots, true)) {
                    continue;
                }

                $eventTime = $this->formatEventTime($date);
                $events[] = new CalendarEventData(
                    title: self::MAX_SLOT_CAPACITY . ' volných míst',
                    start: $eventTime['start'],
                    end: $eventTime['end'],
                    color: 'green',
                );
            }
        }

        return $events;
    }

    /**
     * @return list<int>
     */
    private function getSlotHours(): array
    {
        return [8, 9, 10, 11, 13, 14, 15, 16, 17];
    }

    /**
     * @return array{start: string, end: string}
     */
    private function formatEventTime(DateTime $date, bool $isRestriction = false): array
    {
        $duration = $isRestriction
            ? self::RESTRICTION_DURATION_MINUTES
            : self::SLOT_DURATION_MINUTES;

        return [
            'start' => $date->format('Y-m-d\TH:i:s'),
            'end' => $date->modifyClone('+' . $duration . ' minutes')->format('Y-m-d\TH:i:s'),
        ];
    }

    private function createAvailabilityMessage(int $availableCapacity): string
    {
        if ($availableCapacity <= 0) {
            return 'OBSAZENO';
        }

        if ($availableCapacity === 1) {
            return '1 Volné místo';
        }

        return $availableCapacity . ' Volné místa';
    }

    private function getHasChildrenText(bool $hasChildren, string $default = ''): string
    {
        if ($hasChildren === false) {
            return $default;
        }

        return 'Ano';
    }

    private function resolvePublicEventColor(bool $isRestriction, int $availableCapacity): ?string
    {
        if ($isRestriction) {
            return 'grey';
        }

        if ($availableCapacity <= 0) {
            return 'red';
        }

        if ($availableCapacity < self::MAX_SLOT_CAPACITY) {
            return '#0092ad';
        }

        return null;
    }

    private function resolveHistoricalColor(DateTime $slotDate): ?string
    {
        $now = new DateTime();

        if ($now > $slotDate) {
            return 'grey';
        }

        if ($now->format('Y-m-d H') === $slotDate->format('Y-m-d H')) {
            return 'green';
        }

        return null;
    }
}
