<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * Calendar event view model shared by public and user calendars.
 */
final class CalendarEventData
{
    public function __construct(
        public readonly string $title,
        public readonly string $start,
        public readonly string $end,
        public readonly bool $allDay = false,
        public readonly ?string $color = null,
        public readonly ?string $hasChildren = null,
        public readonly ?int $id = null,
        public readonly ?int $totalCount = null,
        public readonly ?string $date = null,
        public readonly ?string $comment = null,
        public readonly ?int $availableCapacity = null,
    ) {
    }

    /**
     * @return array<string, bool|int|string|null>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'start' => $this->start,
            'end' => $this->end,
            'allDay' => $this->allDay,
            'color' => $this->color,
            'hasChildren' => $this->hasChildren,
            'id' => $this->id,
            'totalCount' => $this->totalCount,
            'date' => $this->date,
            'comment' => $this->comment,
            'availableCapacity' => $this->availableCapacity,
        ];
    }
}
