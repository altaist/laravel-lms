<?php

namespace App\Services\Schedule;

class ScheduleCronManager 
{
    protected string $cron;

    public function __construct(string $cron)
    {
        $this->cron = $cron;
    }

    /**
     * Парсит дни недели из cron-строки.
     *
     * @return array Массив названий дней недели на русском языке.
     * @throws \InvalidArgumentException Если формат cron-строки неверен.
     */
    public function parseCronDays(): array
    {
        $parts = preg_split('/\s+/', $this->cron);

        if (count($parts) !== 5) {
            throw new \InvalidArgumentException('Неверный формат строки cron.');
        }

        $dayOfWeekPart = $parts[4];
        return $this->parsePart($dayOfWeekPart, 'day');
    }

    /**
     * Парсит часы из cron-строки.
     *
     * @return array Массив часов в формате 0-23.
     * @throws \InvalidArgumentException Если формат cron-строки неверен.
     */
    public function parseCronHours(): array
    {
        $parts = preg_split('/\s+/', $this->cron);

        if (count($parts) !== 5) {
            throw new \InvalidArgumentException('Неверный формат строки cron.');
        }

        $hourPart = $parts[1];
        return $this->parsePart($hourPart, 'hour');
    }

    /**
     * Парсит минуты из cron-строки.
     *
     * @return array Массив минут в формате 0-59.
     * @throws \InvalidArgumentException Если формат cron-строки неверен.
     */
    public function parseCronMinutes(): array
    {
        $parts = preg_split('/\s+/', $this->cron);

        if (count($parts) !== 5) {
            throw new \InvalidArgumentException('Неверный формат строки cron.');
        }

        $minutePart = $parts[0];
        return $this->parsePart($minutePart, 'minute');
    }

    /**
     * Парсит дни недели, часы и минуты из cron-строки.
     *
     * @return array Ассоциативный массив с ключами 'days', 'hours', 'minutes'.
     * @throws \InvalidArgumentException Если формат cron-строки неверен.
     */
    public function parseCronAll(): array
    {
        return [
            'days' => $this->parseCronDays(),
            'hours' => $this->parseCronHours(),
            'minutes' => $this->parseCronMinutes(),
        ];
    }

    /**
     * Генерирует cron-строку из массивов дней недели, часов и минут.
     *
     * @param array $days Массив названий дней недели на русском языке.
     * @param array $hours Массив часов в формате 0-23.
     * @param array $minutes Массив минут в формате 0-59.
     * @return string Cron-строка.
     * @throws \InvalidArgumentException Если входные данные некорректны.
     */
    public function generateCron(array $days, array $hours, array $minutes): string
    {
        $minutePart = $this->buildPart($minutes, 'minute');
        $hourPart = $this->buildPart($hours, 'hour');
        $dayPart = $this->buildPart($days, 'day');
        $monthPart = '*'; // Предполагаем, что месяц всегда '*'
        $dayOfWeekPart = '*'; // Предполагаем, что день недели всегда '*'

        // Если определяется день недели, устанавливаем его в пятую позицию
        if (!empty($days)) {
            $dayOfWeekPart = $this->convertDaysToCron($days);
        }

        return sprintf('%s %s %s %s %s', $minutePart, $hourPart, '*', '*', $dayOfWeekPart);
    }

    /**
     * Общий метод для построения части cron-строки из массива значений.
     *
     * @param array $values Массив значений (минуты, часы или дни).
     * @param string $type Тип части ('minute', 'hour', 'day').
     * @return string Часть cron-строки.
     * @throws \InvalidArgumentException Если входные данные некорректны.
     */
    private function buildPart(array $values, string $type): string
    {
        if (empty($values)) {
            return '*';
        }

        // Сортируем и удаляем дубликаты
        $values = array_unique($values);
        sort($values);

        // Для дней недели конвертируем названия в числа
        if ($type === 'day') {
            $values = $this->convertDaysToNumbers($values);
            sort($values);
        }

        // Проверяем, можно ли представить значения диапазоном
        if ($this->isSequential($values)) {
            $start = reset($values);
            $end = end($values);
            if ($start === reset($values) && $end === end($values)) {
                return "{$start}-{$end}";
            }
        }

        // Проверяем шаги
        $step = $this->detectStep($values);
        if ($step > 1) {
            if ($this->isStepFromStart($values, $step)) {
                return "*/{$step}";
            }
        }

        // Возвращаем список значений
        return implode(',', $values);
    }

    /**
     * Преобразует названия дней недели в их числовое представление.
     *
     * @param array $days Массив названий дней недели на русском языке.
     * @return array Массив чисел от 0 до 7.
     * @throws \InvalidArgumentException Если название дня недели некорректно.
     */
    private function convertDaysToNumbers(array $days): array
    {
        $dayMap = [
            'воскресенье' => 0,
            'понедельник' => 1,
            'вторник' => 2,
            'среда' => 3,
            'четверг' => 4,
            'пятница' => 5,
            'суббота' => 6,
        ];

        $numbers = [];

        foreach ($days as $day) {
            $dayLower = mb_strtolower($day);
            if (isset($dayMap[$dayLower])) {
                $numbers[] = $dayMap[$dayLower];
            } else {
                throw new \InvalidArgumentException("Некорректное название дня недели: {$day}.");
            }
        }

        // Удаляем дубликаты и возвращаем
        return array_unique($numbers);
    }

    /**
     * Преобразует массив чисел дней недели в строку для cron.
     *
     * @param array $days Массив названий дней недели на русском языке.
     * @return string Часть cron-строки для дней недели.
     * @throws \InvalidArgumentException Если названия дней некорректны.
     */
    private function convertDaysToCron(array $days): string
    {
        $numbers = $this->convertDaysToNumbers($days);
        sort($numbers);

        // Проверяем, если все дни, то используем '*'
        if (count($numbers) === 7 || (count($numbers) === 8 && in_array(7, $numbers))) {
            return '*';
        }

        // Строим строку из чисел
        return implode(',', $numbers);
    }

    /**
     * Проверяет, являются ли значения последовательными числами.
     *
     * @param array $values Массив чисел.
     * @return bool
     */
    private function isSequential(array $values): bool
    {
        if (count($values) < 2) {
            return false;
        }

        $diff = $values[1] - $values[0];
        if ($diff <= 0) {
            return false;
        }

        foreach ($values as $index => $value) {
            if ($index === 0) {
                continue;
            }
            if ($value - $values[$index - 1] !== $diff) {
                return false;
            }
        }

        return true;
    }

    /**
     * Выявляет шаг между значениями, если он одинаковый.
     *
     * @param array $values Массив чисел.
     * @return int Шаг или 1, если шаг не одинаковый.
     */
    private function detectStep(array $values): int
    {
        if (count($values) < 2) {
            return 1;
        }

        $steps = [];
        for ($i = 1; $i < count($values); $i++) {
            $steps[] = $values[$i] - $values[$i - 1];
        }

        $uniqueSteps = array_unique($steps);
        if (count($uniqueSteps) === 1) {
            return $uniqueSteps[0];
        }

        return 1;
    }

    /**
     * Проверяет, начинается ли массив с шага.
     *
     * @param array $values Массив чисел.
     * @param int $step Шаг.
     * @return bool
     */
    private function isStepFromStart(array $values, int $step): bool
    {
        if (empty($values)) {
            return false;
        }

        $start = $values[0];
        foreach ($values as $index => $value) {
            if ($index === 0) {
                continue;
            }
            if ($value !== $start + ($index * $step)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Преобразует числовое представление дня недели в название на русском языке.
     *
     * @param int $day Число от 0 до 7.
     * @return string Название дня недели.
     */
    private function getDayName(int $day): string
    {
        $days = [
            0 => 'воскресенье',
            1 => 'понедельник',
            2 => 'вторник',
            3 => 'среда',
            4 => 'четверг',
            5 => 'пятница',
            6 => 'суббота',
            7 => 'воскресенье', // Иногда 7 тоже считается воскресеньем
        ];

        return $days[$day] ?? 'неизвестный день';
    }

    /**
     * Общий метод для парсинга части cron-строки.
     *
     * @param string $part Часть cron-строки (минуты, часы или дни).
     * @param string $type Тип части ('minute', 'hour', 'day').
     * @return array Парсенные значения.
     * @throws \InvalidArgumentException Если часть некорректна.
     */
    private function parsePart(string $part, string $type): array
    {
        $values = [];

        // Определение диапазона в зависимости от типа
        switch ($type) {
            case 'minute':
                $min = 0;
                $max = 59;
                break;
            case 'hour':
                $min = 0;
                $max = 23;
                break;
            case 'day':
                $min = 0;
                $max = 7;
                break;
            default:
                throw new \InvalidArgumentException('Неверный тип для парсинга.');
        }

        if ($part === '*') {
            for ($i = $min; $i <= $max; $i++) {
                if ($type === 'day') {
                    $values[] = $this->getDayName($i);
                } else {
                    $values[] = $i;
                }
            }
            return ($type === 'day') ? array_unique($values) : $values;
        }

        $parts = explode(',', $part);

        foreach ($parts as $p) {
            if (strpos($p, '-') !== false) {
                [$start, $end] = explode('-', $p);
                $start = (int)$start;
                $end = (int)$end;

                for ($i = $start; $i <= $end; $i++) {
                    if ($i < $min || $i > $max) {
                        throw new \InvalidArgumentException("Значение {$i} вне допустимого диапазона для {$type}.");
                    }
                    if ($type === 'day') {
                        $values[] = $this->getDayName($i);
                    } else {
                        $values[] = $i;
                    }
                }
            } elseif (strpos($p, '/') !== false) {
                // Обработка шагов, например */15
                [$base, $step] = explode('/', $p);
                $step = (int)$step;

                if ($base !== '*' || $step <= 0) {
                    throw new \InvalidArgumentException("Неверный шаг для {$type}.");
                }

                for ($i = $min; $i <= $max; $i += $step) {
                    if ($type === 'day') {
                        $values[] = $this->getDayName($i);
                    } else {
                        $values[] = $i;
                    }
                }
            } else {
                // Одиночное значение
                $value = (int)$p;
                if ($value < $min || $value > $max) {
                    throw new \InvalidArgumentException("Значение {$value} вне допустимого диапазона для {$type}.");
                }
                if ($type === 'day') {
                    $values[] = $this->getDayName($value);
                } else {
                    $values[] = $value;
                }
            }
        }

        return ($type === 'day') ? array_unique($values) : $values;
    }

    /*
// Создаем экземпляр ScheduleManager с произвольной cron-строкой
$scheduleManager = new ScheduleManager('0 0 * * *');

// Массивы для генерации cron-строки
$days = ['понедельник', 'среда'];
$hours = [14, 16];
$minutes = [30, 45];

// Генерируем cron-строку
$cronString = $scheduleManager->generateCron($days, $hours, $minutes);

// Вывод результата
echo $cronString;
// Ожидаемый вывод: "30,45 14,16 * * 1,3"


    $scheduleManager = new ScheduleManager('* * * * *');
    
    $days = ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'];
    $hours = [8];
    $minutes = [0];
    
    $cronString = $scheduleManager->generateCron($days, $hours, $minutes);
    
    echo $cronString;
    // Ожидаемый вывод: "0 8 * * *"


    $scheduleManager = new ScheduleManager('* * * * *');
    
    $days = ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'];
    $hours = [8];
    $minutes = [0];
    
    $cronString = $scheduleManager->generateCron($days, $hours, $minutes);
    
    echo $cronString;
    // Ожидаемый вывод: "0 8 * * *"

    */
} 