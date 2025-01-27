<?php

namespace App\Services\Schedule;

class ScheduleDaysManager
{
    private array $schedule;
    private array $daysMap = [
        1 => 'Понедельник',
        2 => 'Вторник',
        3 => 'Среда',
        4 => 'Четверг',
        5 => 'Пятница',
        6 => 'Суббота',
        7 => 'Воскресенье'
    ];

    public function __construct(array $data)
    {
        $this->schedule = $data['days'] ?? [];
    }

    /**
     * Получить расписание в текстовом формате
     */
    public function getScheduleText(): string
    {
        if (empty($this->schedule)) {
            return 'Расписание не задано';
        }

        $result = [];
        foreach ($this->schedule as $item) {
            if (count($item) === 3) {
                $dayCode = $item[0];
                $startTime = $item[1];
                $endTime = $item[2];

                $dayName = $this->daysMap[$dayCode] ?? "День {$dayCode}";
                $result[] = "{$dayName}: {$startTime} - {$endTime}";
            }
        }

        return implode(', ', $result);
    }

    /**
     * Получить массив дней недели с расписанием
     */
    public function getScheduleDays(): array
    {
        return array_map(function($item) {
            return [
                'day' => $this->daysMap[$item[0]] ?? "День {$item[0]}",
                'dayCode' => $item[0],
                'startTime' => $item[1],
                'endTime' => $item[2]
            ];
        }, $this->schedule);
    }

    /**
     * Проверить, есть ли занятия в указанный день недели
     */
    public function hasDay(int $dayCode): bool
    {
        foreach ($this->schedule as $item) {
            if ($item[0] === $dayCode) {
                return true;
            }
        }
        return false;
    }

    /**
     * Получить время занятий для конкретного дня
     */
    public function getDaySchedule(int $dayCode): ?array
    {
        foreach ($this->schedule as $item) {
            if ($item[0] === $dayCode) {
                return [
                    'startTime' => $item[1],
                    'endTime' => $item[2]
                ];
            }
        }
        return null;
    }

    /**
     * Получить все дни недели с расписанием
     */
    public function getDays(): array
    {
        return array_map(function($item) {
            return $item[0];
        }, $this->schedule);
    }

    /**
     * Получить оригинальные данные расписания
     */
    public function getRawSchedule(): array
    {
        return $this->schedule;
    }

    /* $data = [
    'days' => [
        [1, '09:00', '10:30'],  // Понедельник
        [3, '11:00', '12:30'],  // Среда
        [5, '15:00', '16:30']   // Пятница
    ]
];

$scheduleManager = new ScheduleDaysManager($data);

// Получить текстовое представление
echo $scheduleManager->getScheduleText();
// Выведет: "Понедельник: 09:00 - 10:30, Среда: 11:00 - 12:30, Пятница: 15:00 - 16:30"

// Проверить наличие занятий в среду
if ($scheduleManager->hasDay(3)) {
    $schedule = $scheduleManager->getDaySchedule(3);
    echo "Занятия в среду: {$schedule['startTime']} - {$schedule['endTime']}";
}
    
$scheduleManager = new ScheduleDaysManager($data);

// Проверка текущей активности
if ($scheduleManager->isActiveNow()) {
    echo "Сейчас идет занятие";
}

// Получение информации о следующем занятии
$nextSchedule = $scheduleManager->getNextSchedule();
if ($nextSchedule) {
    if ($nextSchedule['isToday']) {
        echo "Следующее занятие сегодня в {$nextSchedule['startTime']}";
    } else {
        echo "Следующее занятие в {$nextSchedule['day']} в {$nextSchedule['startTime']}";
    }
}


*/

} 