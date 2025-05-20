<?php

declare(strict_types=1);

namespace App\Model\Service;

use App\Model\Manager\ReservationManager;
use JetBrains\PhpStorm\Deprecated;
use Nette\Security\User;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;

use function array_merge;
use function in_array;
use function range;

#[Deprecated('Will be replaced')]
class CalendarServiceOld
{
    public function __construct(
        private readonly ReservationManager $reservationManager,
        private readonly User $user
    )
    {
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = $this->reservationManager->findAllReservationsFormatted()->fetchAll();
        $calendarSlots = [];
        $usedSlots = [];

        foreach ($data as $dat)
        {
            $count = $dat['SUM(`count`)'];
            $usedSlots[] = $dat->date;
            $date = $this->formatCalendarDate($dat->date, $dat->name);
            $message = $this->createCalendarMessage($count);
            $calendarSlots[] = [
                'title'       => ($dat->name !== 'restriction') ? $message . CalendarServiceOld::getHasChildrenText(
                        (bool)$dat->hasChildren
                    ) : 'Omezeni provozu',
                'start'       => $date->fullStart,
                'end'         => $date->fullEnd,
                'hasChildren' => CalendarServiceOld::getHasChildrenText((bool)$dat->hasChildren),
                'allDay'      => false,
                'color'       => $this->getEventColor($message, $dat->name),
            ];
        }
        $emptySlots = $this->fillEmptyDates($usedSlots);

        return array_merge($emptySlots, $calendarSlots);
    }

    private function fillEmptyDates($usedSlots)
    {
        $data = [];
        $restrictions = $this->reservationManager->findReservationsPairs();

        foreach (range(0, 99) as $i)
        { // 100 Days
            $calculationDate = new DateTime("now + {$i}days");
            $calculationDate->setTime(8, 0, 0);

            if (in_array($calculationDate, $restrictions))
            {
                continue; // Skip day if restriction
            }

            foreach (range(8, 17) as $x)
            { // 9 slots for each day
                $skipSlot = false;
                if ($x === 12)
                {
                    continue; // lunch break
                }
                $calculationDate->setTime($x, 0, 0);
                foreach ($usedSlots as $usedSlotDate)
                { // Is slot used ?
                    if ($usedSlotDate == $calculationDate)
                    {
                        $skipSlot = true;
                        continue;
                    }
                }

                if ($skipSlot === false)
                { // Slot not used fill with empty
                    $date = $this->formatCalendarDate($calculationDate, null);
                    $data[] = [
                        'title'  => '5 volných míst',
                        'start'  => $date->fullStart,
                        'end'    => $date->fullEnd,
                        'child'  => '',
                        'allDay' => false,
                        'color'  => 'green',
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getUserData()
    {
        $data = $this->reservationManager->findReservationsByUser($this->user->id);
        $array = [];
        foreach ($data as $dat)
        {
            $date = $this->formatCalendarDate($dat->date, null);
            $array[] = [
                'title'       => 'Rezervováno (' . $dat->count . ')',
                'start'       => $date->fullStart,
                'id'          => $dat->id,
                'end'         => $date->fullEnd,
                'color'       => $this->getAdminColor(new DateTime($date->originalDate)),
                'totalCount'  => $dat->count,
                'date'        => $date->timeStart,
                'hasChildren' => (bool)$dat->has_children ? 'Ano' : 'Ne',
                'comment'     => $dat->comment,
            ];
        }

        return $array;
    }

    /**
     * @return array
     */
    #[Deprecated('Use grid instead of data')]
    public function getAdminData()
    {
        $data = $this->reservationManager->getEntityTable();
        $array = [];
        foreach ($data as $dat)
        {
            $restricted = false;
            $texts = $this->checkTexts($dat);

            $array[] = [
                'title'       => $texts->name . ' (' . $dat->count . 'x)',
                'start'       => $texts->date->fullStart,
                'end'         => $texts->date->fullEnd,
                'color'       => $this->getAdminColor(new DateTime($texts->date->originalDate)),
                'userName'    => $texts->name,
                'totalCount'  => $dat->count,
                'telephone'   => $texts->telephone,
                'email'       => $texts->email,
                'id'          => $dat->id,
                'date'        => $texts->date->timeStart,
                'hasChildren' => $texts->has_children,
                'comment'     => $dat->comment,
            ];
        }

        return $array;
    }

    /**
     * @param DateTime $dbDate
     *
     * @return ArrayHash
     */
    private function formatCalendarDate($dbDate, $restriction)
    {
        $dbDate = (string)$dbDate;
        $dateStart = new \DateTime($dbDate);
        $result = new ArrayHash();
        $restricted = false;

        if ($restriction === 'restriction')
        {
            $restricted = true;
        }

        if ($restricted === true)
        {
            $endDate = date('H:i', strtotime($dbDate) + (60 * 600));
            $fullEnd = date('Y-m-d\TH:i:s', strtotime($dbDate) + (60 * 600));
        } else
        {
            $endDate = date('H:i', strtotime($dbDate) + (60 * 45));
            $fullEnd = date('Y-m-d\TH:i:s', strtotime($dbDate) + (60 * 45));
        }

        $result->originalDate = $dbDate;
        $result->fullStart = $dateStart->format('Y-m-d\TH:i:s');
        $result->fullEnd = $fullEnd;
        $result->timeStart = $dateStart->format('H:i');
        $result->timeEnd = $endDate;

        return $result;
    }

    public function checkTexts($data): ArrayHash
    {
        $result = new ArrayHash();
        if (isset($data->name))
        {
            $result->name = $data->name;
            $result->email = $data->email;
            $result->telephone = $data->telefon;
        } else
        {
            $result->name = 'name'; //$data->user->name;
            $result->email = 'email'; //$data->user->email;
            $result->telephone = 'telephone'; //$data->user->telephone;
        }
        $result->hasChildren = CalendarServiceOld::getHasChildrenText((bool)$data->hasChildren);
        $result->date = $this->formatCalendarDate($data->date, $result->name);

        return $result;
    }

    /**
     * @param bool $hasChildren
     *
     * @return null|string
     */
    private static function getHasChildrenText(bool $hasChildren): ?string
    {
        if ($hasChildren === false)
        {
            return '';
        }

        return ' (Děti: Ano)';
    }

    /**
     * @param $rezervations
     *
     * @return string
     */
    private function createCalendarMessage($rezervations)
    {
        if (5 - $rezervations == 1)
        {
            $message = 5 - $rezervations . ' Volné místo';
        } else
        {
            $message = 5 - $rezervations . ' Volné místa';
        }
        if (5 - $rezervations == 0)
        {
            $message = 'OBSAZENO';
        }

        return $message;
    }

    /**
     * @return null|string
     */
    private function getAdminColor(DateTime $dateStart)
    {
        $color = null;
        $actualDate = new DateTime();
        if ($actualDate > $dateStart)
        {
            $color = 'grey';
        }
        if ($actualDate->format('Y-m-d H') == $dateStart->format('Y-m-d H'))
        {
            $color = 'green';
        }

        return $color;
    }

    /**
     * @param $message
     *
     * @return null|string
     */
    private function getEventColor($message, $name)
    {
        if ($name === 'restriction')
        {
            return 'grey';
        }

        if ($message === 'OBSAZENO')
        {
            return 'red';
        } else
        {
            return null;
        }
    }
}
