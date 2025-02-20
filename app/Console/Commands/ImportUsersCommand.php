<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;
use App\Services\LoginLinkService;
use App\Enums\UserRoleEnum;
use App\Services\CreditService;
use App\Services\BalanceService;
use App\Enums\CoinEnum;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ImportUsersCommand extends Command
{
    private const SYSTEM_USER_ID = 1;
    private const RELATED_TABLES = [
        'team_user',
        'credits',
        'balances',
        'payments',
        'login_tokens'
    ];

    protected $signature = 'app:import-users 
        {--sync : Удалить записи, которых нет в CSV}
        {--clear : Удалить всех пользователей (id>' . User::SYSTEM_USERS_MAX_ID . ') перед импортом}
        {--file=import.csv : Название файла для импорта}';
        
    protected $description = 'Импорт пользователей и команд из CSV файла';

    private ?int $studentRoleId = null;

    public function __construct(
        private UserService $userService,
        private LoginLinkService $loginLinkService,
        private CreditService $creditService,
        private BalanceService $balanceService,
        private ScheduleService $scheduleService
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        Auth::loginUsingId(self::SYSTEM_USER_ID);

        if ($this->option('clear') && !$this->clearUsers()) {
            return;
        }

        if (!$csvFile = $this->openCsvFile()) {
            return;
        }

        $this->processImport($csvFile);
    }

    private function clearUsers(): bool
    {
        if (!$this->confirm('Вы уверены, что хотите удалить всех пользователей (id>' . User::SYSTEM_USERS_MAX_ID . ')?')) {
            $this->info('Операция отменена');
            return false;
        }

        return $this->deleteUsers(
            User::where('id', '>', User::SYSTEM_USERS_MAX_ID)->pluck('id')->toArray()
        );
    }

    private function openCsvFile()
    {
        $filename = $this->option('file');
        $filepath = base_path("database/data/{$filename}");
        
        if (!file_exists($filepath)) {
            $this->error("Файл не найден: database/data/{$filename}");
            return false;
        }
        
        $csvFile = fopen($filepath, "r");
        if ($csvFile === false) {
            $this->error("Не удалось открыть файл: database/data/{$filename}");
            return false;
        }

        // Пропускаем заголовок
        $headers = fgetcsv($csvFile);
        if ($headers === false) {
            $this->error("Файл пуст или имеет неверный формат");
            fclose($csvFile);
            return false;
        }

        return ['file' => $csvFile, 'headers' => $headers];
    }

    private function processImport(array $csvData): void
    {
        $processedIds = [];
        $rowCount = 0;
        $errorCount = 0;
        
        $this->info("Начало импорта из файла: {$this->option('file')}");
        
        DB::beginTransaction();
        try {
            while (($row = fgetcsv($csvData['file'])) !== false) {
                $data = array_combine($csvData['headers'], $row);
                
                if (!$this->isValidEmail($data)) {
                    $errorCount++;
                    continue;
                }
                
                $user = $this->processUser($data);
                $processedIds[] = $user->id;
                
                $this->processTeam($data, $user);
                
                $rowCount++;
            }
            
            if ($this->option('sync')) {
                $this->syncUsers($processedIds);
            }
            
            DB::commit();
            $this->info("Импорт завершен. Обработано строк: {$rowCount}, Ошибок: {$errorCount}");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Произошла ошибка при импорте: " . $e->getMessage());
        } finally {
            fclose($csvData['file']);
        }
    }

    private function isValidEmail(array $data): bool
    {
        $email = !empty($data['email']) 
            ? $data['email'] 
            : Str::slug($data['name']) . '@example.fakeemail';
            
        if (User::where('email', $email)
                ->where('id', '!=', $data['id'] ?? 0)
                ->exists()) {
            $this->warn("Пропуск строки: email {$email} уже существует");
            return false;
        }
        
        return true;
    }

    private function deleteUsers(array $userIds): bool
    {
        if (empty($userIds)) {
            $this->info("Нет пользователей для удаления");
            return true;
        }

        DB::beginTransaction();
        try {
            // Обновляем author_id в credits
            DB::table('credits')
                ->whereIn('user_id', $userIds)
                ->update(['author_id' => self::SYSTEM_USER_ID]);

            // Удаляем связанные данные
            foreach (self::RELATED_TABLES as $table) {
                DB::table($table)->whereIn('user_id', $userIds)->delete();
            }
            
            // Удаляем пользователей
            $deletedCount = User::whereIn('id', $userIds)->delete();
            
            DB::commit();
            $this->info("Удалено пользователей: {$deletedCount}");
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Ошибка при удалении пользователей: " . $e->getMessage());
            return false;
        }
    }

    private function syncUsers(array $processedIds): void
    {
        $userIds = User::where('id', '>', User::SYSTEM_USERS_MAX_ID)
            ->whereNotIn('id', $processedIds)
            ->pluck('id')
            ->toArray();

        $this->deleteUsers($userIds);
    }

    private function processUser(array $data): User
    {
        // Генерируем уникальный email
        $email = $this->generateUniqueEmail($data);
            
        // Генерируем пароль
        $password = Str::random(8);
        
        // Формируем данные для создания/обновления пользователя
        $userData = [
            'name' => $data['name'],
            'email' => $email,
            'password' => $password,
            'role_id' => $this->getRoleId($data),
            'status' => $data['status'] ?? 1,
            'key' => Str::random(32),
            'person' => [
                'last_name' => explode(' ', $data['person.fio'])[0] ?? '',
                'first_name' => explode(' ', $data['person.fio'])[1] ?? '',
                'parent_tel' => $data['person.parent_tel'],
                'parent_fio' => $data['person.parent_fio'],
                'birth_date' => null,
                'gender' => $this->normalizeGender($data['person.gender'] ?? null),
                'shift' => $this->processShift($data),
                'shift_comment' => $this->getShiftComment($data),
            ],
            'settings' => [
                'theme' => 'light',
                'notifications' => true
            ],
            'statistic' => [
                'last_login' => now(),
                'login_count' => 0
            ]
        ];

        // Добавляем номер карты если он есть в CSV
        if (!empty($data['card_number'])) {
            $userData['card_number'] = $data['card_number'];
            $userData['card_delivered_at'] = '2025-01-01 00:00:00';
        }

        // Ищем существующего пользователя если есть ID
        $user = !empty($data['id']) ? User::find($data['id']) : null;

        // Создаем или обновляем пользователя через сервис
        $user = $this->userService->createOrUpdate($userData, $user);

        // Добавляем кредиты, если они указаны
        if (!empty($data['credits']) && is_numeric($data['credits'])) {
            $this->processCredits($user, (int)$data['credits']);
        }

        // Генерируем ссылку для входа
        $loginLink = $this->loginLinkService->generateFor($user);

        // Выводим информацию о созданном пользователе
        $this->info("Пользователь {$user->name}:");
        $this->info("- Пароль: {$password}");
        $this->info("- Ссылка для входа: {$loginLink}");

        return $user;
    }

    private function processTeam(array $data, User $user): void
    {
        // Проверяем существование команды
        $team = Team::firstOrCreate(
            ['name' => $data['team_name']],
            [
                'type' => $data['team_type'] ?? 'default',
                'description' => $data['team_description'] ?? '',
            ]
        );
        
        // Привязываем пользователя к команде
        $team->users()->attach($user->id);

        // Обрабатываем расписание, если есть данные
        if (!empty($data['schedule.duration']) && 
            !empty($data['schedule1.day_of_week']) && 
            !empty($data['schedule1.start_time'])) {
            $this->processSchedule($team, $data);
        }
    }

    /**
     * Обрабатывает расписание команды
     */
    private function processSchedule(Team $team, array $data): void
    {
        $scheduleDays = [];

        // Формируем первый день расписания
        $startTime = $this->normalizeTime($data['schedule1.start_time']);
        $endTime = Carbon::createFromFormat('H:i', $startTime)
            ->addMinutes((int)$data['schedule.duration'])
            ->format('H:i');
            
        $scheduleDays[] = [
            'day_of_week' => (int)$data['schedule1.day_of_week'],
            'start_time' => $startTime,
            'end_time' => $endTime
        ];

        // Добавляем второй день расписания, если он задан
        if (!empty($data['schedule2.day_of_week']) && !empty($data['schedule2.start_time'])) {
            $startTime = $this->normalizeTime($data['schedule2.start_time']);
            $endTime = Carbon::createFromFormat('H:i', $startTime)
                ->addMinutes((int)$data['schedule.duration'])
                ->format('H:i');

            $scheduleDays[] = [
                'day_of_week' => (int)$data['schedule2.day_of_week'],
                'start_time' => $startTime,
                'end_time' => $endTime
            ];
        }

        try {
            // Обновляем расписание через сервис
            $result = $this->scheduleService->updateSchedule($team, $scheduleDays);

            // Выводим информацию о созданном расписании
            foreach ($result['schedule_days'] as $day) {
                $this->info("Добавлен день расписания для команды {$team->name}: " . 
                    "день {$day->day_of_week}, " . 
                    "начало {$day->start_time}, " . 
                    "конец {$day->end_time}");
            }
        } catch (\Exception $e) {
            $this->error("Ошибка при обновлении расписания для команды {$team->name}: {$e->getMessage()}");
        }
    }

    /**
     * Нормализует время в формат H:i
     */
    private function normalizeTime(string $time): string
    {
        // Убираем все пробелы
        $time = trim($time);

        // Если время задано только в часах, добавляем минуты
        if (preg_match('/^\d{1,2}$/', $time)) {
            $time .= ':00';
        }

        // Если в формате H:i, но одна цифра для часов, добавляем ведущий ноль
        if (strlen($time) === 4 && strpos($time, ':') === 1) {
            $time = '0' . $time;
        }

        // Проверяем корректность времени
        try {
            Carbon::createFromFormat('H:i', $time);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Некорректный формат времени: {$time}");
        }

        return $time;
    }

    private function getRoleId(array $data): int
    {
        // Если роль задана, используем её
        if (!empty($data['role_id']) && $data['role_id'] != '') {
            return $data['role_id'];
        }

        // По умолчанию устанавливаем роль STUDENT
        return UserRoleEnum::STUDENT->value;
    }

    /**
     * Генерирует уникальный email для пользователя
     */
    private function generateUniqueEmail(array $data): string 
    {
        // Если email задан, пробуем использовать его
        $baseEmail = !empty($data['email']) 
            ? $data['email'] 
            : Str::slug($data['name']) . '@example.fakeemail';

        $email = $baseEmail;
        $counter = 1;

        // Проверяем существование email, исключая текущего пользователя
        while (User::where('email', $email)
                ->where('id', '!=', $data['id'] ?? 0)
                ->exists()) {
            // Добавляем счетчик перед @ в email
            $parts = explode('@', $baseEmail);
            $email = $parts[0] . $counter . '@' . $parts[1];
            $counter++;
        }

        return $email;
    }

    /**
     * Добавляет кредиты пользователю и обновляет баланс
     */
    private function processCredits(User $user, int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        // Обновляем кредиты и баланс в одной транзакции
        $this->balanceService->updateCreditAndBalance(
            $user,
            $user->id,
            CoinEnum::LESSON->value, // Используем enum вместо хардкода
            $amount,
            1 // reason_id
        );

        $this->info("Добавлено {$amount} " . CoinEnum::LESSON->shortName() . " для пользователя {$user->name}");
    }

        /**
     * Нормализует значение пола
     */
    private function normalizeGender(?string $gender): ?string
    {
        if (empty($gender)) {
            return null;
        }

        $gender = mb_strtolower(trim($gender));
        
        return match($gender) {
            'м', 'm', 'муж', 'мужской', 'male' => 'male',
            'ж', 'f', 'жен', 'женский', 'female' => 'female',
            default => null
        };
    }

    /**
     * Обрабатывает значение смены
     */
    private function processShift(array $data): ?int
    {
        if (empty($data['person.shift'])) {
            return null;
        }

        $shift = trim($data['person.shift']);

        // Если значение числовое, возвращаем его
        if (is_numeric($shift)) {
            return (int)$shift;
        }

        // Если не числовое, возвращаем null
        return null;
    }

    /**
     * Получает комментарий к смене
     */
    private function getShiftComment(array $data): ?string
    {
        if (empty($data['person.shift'])) {
            return null;
        }

        $shift = trim($data['person.shift']);

        // Если значение не числовое, возвращаем его как комментарий
        if (!is_numeric($shift)) {
            return $shift;
        }

        return null;
    }
} 