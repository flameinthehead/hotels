<?php

namespace App\UseCase\Telegram;

use Illuminate\Support\Facades\Log;

class Calendar
{
    private array $calendarData = [];

    private const DAY_IN_A_WEEK = 7;
    private const NEXT_MONTH_PREFIX = 'next-month';
    private const PREV_MONTH_PREFIX = 'prev-month';
    private const SELECTED_DATE_PREFIX = 'select-date';

    public const RU_MONTH_LIST = [
        '01' => 'январь',
        '02' => 'февраль',
        '03' => 'март',
        '04' => 'апрель',
        '05' => 'май',
        '06' => 'июнь',
        '07' => 'июль',
        '08' => 'август',
        '09' => 'сентябрь',
        '10' => 'октябрь',
        '11' => 'ноябрь',
        '12' => 'декабрь',
    ];

    public const RU_DAY_OF_WEEK_LIST = [
        1 => 'Пн',
        2 => 'Вт',
        3 => 'Ср',
        4 => 'Чт',
        5 => 'Пт',
        6 => 'Сб',
        7 => 'Вс',
    ];

    public function makeCalendar(string $callBackData = null): array
    {
        $date = $this->parseDate($callBackData);

        $this->addMonthName($date->format('m'));
        $this->addWeekDayNames();
        $this->addMonthNumbers($date);
        $this->addArrows($date);

        return $this->calendarData;
    }

    public function isSelectedDate(string $callBackData): bool
    {
        return mb_strpos($callBackData, self::SELECTED_DATE_PREFIX) !== false;
    }

    public function parseDate(string $callBackData = null): \DateTimeImmutable
    {
        if (empty($callBackData)) {
            return new \DateTimeImmutable();
        }

        if (mb_strpos($callBackData, self::PREV_MONTH_PREFIX) !== false) {
            $dateStr = str_replace(self::PREV_MONTH_PREFIX.'-', '', $callBackData);
        } elseif(mb_strpos($callBackData, self::NEXT_MONTH_PREFIX) !== false) {
            $dateStr = str_replace(self::NEXT_MONTH_PREFIX . '-', '', $callBackData);
        } elseif(mb_strpos($callBackData, self::SELECTED_DATE_PREFIX) !== false) {
            $dateStr = str_replace(self::SELECTED_DATE_PREFIX . '-', '', $callBackData);
        } else {
            throw new \Exception('Ошибка при парсинге выбранной даты');
        }

        return new \DateTimeImmutable($dateStr);
    }

    private function addMonthName(string $monthName): void
    {
        $this->calendarData[] = [
            [
                'text' => self::RU_MONTH_LIST[$monthName],
                'callback_data'=> 'month-'.$monthName,
            ]
        ];
    }

    private function addWeekDayNames(): void
    {
        $dayOfWeekRow = [];
        for ($i = 1; $i <= self::DAY_IN_A_WEEK; ++$i) {
            $dayOfWeekRow[] = [
                'text' => self::RU_DAY_OF_WEEK_LIST[$i],
                'callback_data'=> 'day-'.$i,
            ];
        }
        $this->calendarData[] = $dayOfWeekRow;
    }

    private function addMonthNumbers(\DateTimeImmutable $date): void
    {
        $dayOfMonth = 1;
        $row = [];
        $isPushedFirstNumber = false;
        $now = new \DateTimeImmutable();
        while (true) {
            $monthDay = new \DateTimeImmutable($dayOfMonth.'-'.$date->format('m') .'-'.$date->format('o'));
            if ($monthDay < $now) {
                ++$dayOfMonth;
                continue;
            }

            $monthDayNumber = $monthDay->format('N');

            for ($day = 1; $day <= self::DAY_IN_A_WEEK; ++$day) {
                if($monthDayNumber == $day) {
                    $row[] = [
                        'text' => $monthDay->format('j'),
                        'callback_data' => self::SELECTED_DATE_PREFIX.'-'.$monthDay->format('d.m.Y'),
                    ];
                    $isPushedFirstNumber = true;
                    break;
                }

                if(empty($isPushedFirstNumber)) {
                    $this->addEmptyButton($row);
                }
            }

            if(count($row) == self::DAY_IN_A_WEEK) {
                $this->calendarData[] = $row;
                $row = [];
            }

            ++$dayOfMonth;

            if ($dayOfMonth == $date->format('t')) {
                $this->addFinalEmptyButtons($row);
                break;
            }
        }
    }

    private function addEmptyButton(&$data = []): void
    {
        $data[] = [
            'text' => ' ',
            'callback_data' => ' ',
        ];
    }

    private function addFinalEmptyButtons(array $row): void
    {
        if (!empty($row)) {
            if(count($row) < self::DAY_IN_A_WEEK) {
                for($counter = count($row); $counter < self::DAY_IN_A_WEEK; ++$counter) {
                    $this->addEmptyButton($row);
                }
            }
            $this->calendarData[] = $row;
        }
    }

    private function addArrows(\DateTimeImmutable $date): void
    {
        $prevMonth = [];

        if($date->modify('last day of previous month') >= new \DateTimeImmutable()) {
            $prevMonth = [
                'text' => '<',
                'callback_data' => self::PREV_MONTH_PREFIX . '-' . $date->modify('-1 month')->format('Y-m') . '-1',
            ];
        }

        $arrows = [
            $prevMonth,
            [
                'text' => '>',
                'callback_data' => self::NEXT_MONTH_PREFIX . '-' . $date->modify('+1 month')->format('Y-m') . '-1',
            ],
        ];

        Log::debug(var_export(array_values(array_filter($arrows)), true));

        $this->calendarData[] = array_values(array_filter($arrows));
    }
}
