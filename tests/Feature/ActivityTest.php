<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Team;
use App\Models\Activity;
use App\Models\Balance;
use App\Models\Credit;
use App\Enums\UserRoleEnum;
use App\Enums\CoinEnum;
use App\Enums\CreditReasonEnum;
use Database\Seeders\DictSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;
    private Team $team;
    private array $students;
    private Activity $activity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DictSeeder::class);

        // Создаем учителя
        $this->teacher = User::factory()->create([
            'role_id' => UserRoleEnum::TEACHER
        ]);

        // Авторизуем учителя
        $this->actingAs($this->teacher);

        // Создаем команду
        $this->team = Team::factory()->create();

        // Создаем студентов
        $students = User::factory(3)
            ->create(['role_id' => UserRoleEnum::STUDENT]);
            
        // Привязываем студентов к команде и сохраняем как массив
        $this->students = $students->each(function ($student) {
            $this->team->users()->attach($student->id);
        })->toArray();

        // Создаем активность
        $this->activity = Activity::factory()->create([
            'team_id' => $this->team->id,
            'status' => Activity::STATUS_PENDING
        ]);

        // Создаем начальные кредиты для каждого студента
        foreach ($students as $student) {
            // Создаем кредит на 10 единиц
            Credit::create([
                'author_id' => $this->teacher->id,
                'user_id' => $student->id,
                'coin_id' => CoinEnum::LESSON->value,
                'amount' => 10,
                'creditable_type' => Activity::class,
                'creditable_id' => $this->activity->id,
                'reason_id' => CreditReasonEnum::PAYMENT->value,
                'description' => 'Initial credit'
            ]);

            // Проверяем баланс после создания кредита
            $balance = Credit::where('user_id', $student->id)
                ->where('coin_id', CoinEnum::LESSON->value)
                ->sum('amount');

            $this->assertEquals(10, $balance, 'Начальный баланс должен быть 10');
        }

        $this->assertEquals(3, $this->activity->team->students->count());


    }

    #[Test]
    public function it_can_start_and_stop_activity()
    {
        // Запускаем активность
        $response = $this->post("/activities/{$this->activity->id}/start");
        $response->assertStatus(200);
        
        $this->activity->refresh();
        $this->assertEquals(Activity::STATUS_STARTED, $this->activity->status);
        $this->assertNotNull($this->activity->started_at);

        // Останавливаем активность
        $response = $this->post("/activities/{$this->activity->id}/stop");
        $response->assertStatus(200);
        
        $this->activity->refresh();
        $this->assertNotNull($this->activity->finished_at);

        // Проверяем, что у каждого студента списался 1 коин
        foreach ($this->students as $student) {
            // Проверяем сумму всех кредитов
            $totalCredits = Credit::where('user_id', $student['id'])
                ->where('coin_id', CoinEnum::LESSON->value)
                ->sum('amount');
            
            $this->assertEquals(9, $totalCredits, 'Сумма кредитов должна быть 9 после списания');

            // Проверяем запись о списании
            $debitCredit = Credit::where([
                'user_id' => $student['id'],
                'creditable_type' => Activity::class,
                'creditable_id' => $this->activity->id,
                'coin_id' => CoinEnum::LESSON->value,
                'reason_id' => CreditReasonEnum::LESSON->value
            ])->first();

            $this->assertNotNull($debitCredit, 'Должна быть запись о списании');
            $this->assertEquals(-1, $debitCredit->amount, 'Должен быть списан 1 коин');
        }
    }

    #[Test]
    public function it_can_restart_activity()
    {
        // Сначала запускаем и останавливаем активность
        $this->post(route('activities.start', $this->activity));
        // Проверяем списание после первого цикла
        foreach ($this->students as $student) {
            $totalCredits = Credit::where('user_id', $student['id'])
                ->where('coin_id', CoinEnum::LESSON->value)
                ->sum('amount');
            
            $this->assertEquals(10, $totalCredits, 'Сумма кредитов должна быть 9 после списания');
        }
        $this->post(route('activities.stop', $this->activity));

        // Проверяем списание после первого цикла
        foreach ($this->students as $student) {
            $totalCredits = Credit::where('user_id', $student['id'])
                ->where('coin_id', CoinEnum::LESSON->value)
                ->sum('amount');
            
            $this->assertEquals(9, $totalCredits, 'Сумма кредитов должна быть 9 после списания');
        }

        // Рестарт активности
        $this->post(route('activities.restart', $this->activity));

        // Проверяем после рестарта
        foreach ($this->students as $student) {
            // Проверяем сумму всех кредитов
            $totalCredits = Credit::where('user_id', $student['id'])
                ->where('coin_id', CoinEnum::LESSON->value)
                ->sum('amount');
            
            $this->assertEquals(10, $totalCredits, 'Сумма кредитов должна вернуться к 10');

            // Проверяем запись о возврате
            $refundCredit = Credit::where([
                'user_id' => $student['id'],
                'creditable_type' => Activity::class,
                'creditable_id' => $this->activity->id,
                'reason_id' => CreditReasonEnum::LESSON_CANCELLED->value
            ])->first();

            $this->assertNotNull($refundCredit, 'Должна быть запись о возврате');
            $this->assertEquals(1, $refundCredit->amount, 'Должен быть возвращен 1 коин');
        }

        $this->activity->refresh();
        $this->assertEquals(Activity::STATUS_PENDING, $this->activity->status);
        $this->assertNull($this->activity->started_at);
        $this->assertNull($this->activity->finished_at);
    }

    #[Test]
    public function it_cannot_deduct_credits_from_finished_activity()
    {
        // Запускаем и останавливаем активность
        $this->post("/activities/{$this->activity->id}/start");
        $this->post("/activities/{$this->activity->id}/stop");

        // Пытаемся остановить еще раз
        $response = $this->post("/activities/{$this->activity->id}/stop");
        $response->assertStatus(422);

        // Проверяем, что баланс не изменился повторно
        foreach ($this->students as $student) {
            $balance = Balance::where('user_id', $student['id'])
                ->where('coin_id', CoinEnum::LESSON->value)
                ->first();
            
            $this->assertEquals(9, $balance->amount);

            // Проверяем, что есть только одна запись кредита
            $creditsCount = Credit::where([
                'user_id' => $student['id'],
                'creditable_type' => Activity::class,
                'creditable_id' => $this->activity->id
            ])->count();

            $this->assertEquals(1, $creditsCount);
        }
    }

    #[Test]
    public function it_can_attach_and_detach_students()
    {
        // Создаем нового студента
        $newStudent = User::factory()->create([
            'role_id' => UserRoleEnum::STUDENT
        ]);

        // Добавляем студента к активности
        $response = $this->post('/activities/add-students', [
            'activityId' => $this->activity->id,
            'studentIds' => [$newStudent->id]
        ]);
        $response->assertStatus(200);

        $this->activity->refresh();
        $this->assertTrue($this->activity->users->contains($newStudent->id));

        // Удаляем студента из активности
        $response = $this->post('/activities/remove-students', [
            'activityId' => $this->activity->id,
            'studentIds' => [$newStudent->id]
        ]);
        $response->assertStatus(200);

        $this->activity->refresh();
        $this->assertFalse($this->activity->users->contains($newStudent->id));
    }

    #[Test]
    public function it_deducts_credits_from_all_team_students_regardless_of_activity_attachment()
    {
        // Создаем нового студента в команде, но не прикрепляем к активности
        $newStudent = User::factory()->create([
            'role_id' => UserRoleEnum::STUDENT
        ]);
        $this->team->users()->attach($newStudent->id);
        
        Balance::create([
            'user_id' => $newStudent->id,
            'coin_id' => CoinEnum::LESSON->value,
            'amount' => 10
        ]);

        // Запускаем и останавливаем активность
        $this->post("/activities/{$this->activity->id}/start");
        $this->post("/activities/{$this->activity->id}/stop");

        // Проверяем, что кредиты списались у всех студентов команды
        $allTeamStudents = $this->team->students()->get();
            
        foreach ($allTeamStudents as $student) {
            $balance = Balance::where('user_id', $student->id)
                ->where('coin_id', CoinEnum::LESSON->value)
                ->first();
            
            $this->assertEquals(9, $balance->amount);

            $credit = Credit::where([
                'user_id' => $student->id,
                'creditable_type' => Activity::class,
                'creditable_id' => $this->activity->id
            ])->first();

            $this->assertNotNull($credit);
            $this->assertEquals(-1, $credit->amount);
        }
    }

    #[Test]
    public function it_handles_multiple_start_stop_cycles_correctly()
    {
        // Первый цикл: старт-стоп
        $this->post(route('activities.start', $this->activity));
        $this->post(route('activities.stop', $this->activity));

        // Проверяем списание после первого цикла
        foreach ($this->students as $student) {
            // Проверяем запись в кредитах
            $debitCredit = Credit::where([
                'user_id' => $student['id'],
                'creditable_type' => Activity::class,
                'creditable_id' => $this->activity->id,
                'coin_id' => CoinEnum::LESSON->value,
                'reason_id' => CreditReasonEnum::LESSON->value
            ])->first();

            $this->assertNotNull($debitCredit, 'Должна быть запись о списании кредитов');
            $this->assertEquals(-1, $debitCredit->amount, 'Должен быть списан 1 коин');

            // Проверяем итоговый баланс
            $balance = Balance::where('user_id', $student['id'])
                ->where('coin_id', CoinEnum::LESSON->value)
                ->first();
            
            $this->assertEquals(9, $balance->amount, 'Баланс должен быть 9 после списания');
        }

        // Рестарт активности
        $this->post(route('activities.restart', $this->activity));

        // Проверяем возврат коинов после рестарта
        foreach ($this->students as $student) {
            // Проверяем запись о возврате кредитов
            $refundCredit = Credit::where([
                'user_id' => $student['id'],
                'creditable_type' => Activity::class,
                'creditable_id' => $this->activity->id,
                'coin_id' => CoinEnum::LESSON->value,
                'reason_id' => CreditReasonEnum::LESSON_CANCELLED->value
            ])->first();

            $this->assertNotNull($refundCredit, 'Должна быть запись о возврате кредитов');
            $this->assertEquals(1, $refundCredit->amount, 'Должен быть возвращен 1 коин');

            // Проверяем итоговый баланс
            $balance = Balance::where('user_id', $student['id'])
                ->where('coin_id', CoinEnum::LESSON->value)
                ->first();
            
            $this->assertEquals(10, $balance->amount, 'Баланс должен вернуться к 10 после рестарта');
        }

        // Второй цикл: старт-стоп
        $this->post(route('activities.start', $this->activity));
        $this->post(route('activities.stop', $this->activity));

        // Проверяем списание после второго цикла
        foreach ($this->students as $student) {
            // Проверяем новую запись в кредитах
            $newDebitCredit = Credit::where([
                'user_id' => $student['id'],
                'creditable_type' => Activity::class,
                'creditable_id' => $this->activity->id,
                'coin_id' => CoinEnum::LESSON->value,
                'reason_id' => CreditReasonEnum::LESSON->value
            ])->orderBy('id', 'desc')->first();

            $this->assertNotNull($newDebitCredit, 'Должна быть новая запись о списании кредитов');
            $this->assertEquals(-1, $newDebitCredit->amount, 'Должен быть списан 1 коин');

            // Проверяем итоговый баланс
            $balance = Balance::where('user_id', $student['id'])
                ->where('coin_id', CoinEnum::LESSON->value)
                ->first();
            
            $this->assertEquals(9, $balance->amount, 'Баланс должен быть 9 после второго списания');
        }
    }

    #[Test]
    public function it_handles_insufficient_balance_correctly()
    {
        // Устанавливаем отрицательный баланс
        foreach ($this->students as $student) {
            Balance::where('user_id', $student['id'])
                ->where('coin_id', CoinEnum::LESSON->value)
                ->update(['amount' => -5]);
        }

        // Проводим активность
        $this->post(route('activities.start', $this->activity));
        $response = $this->post(route('activities.stop', $this->activity));
        
        $response->assertStatus(200);

        // Проверяем, что кредиты списались и баланс стал еще более отрицательным
        foreach ($this->students as $student) {
            // Проверяем запись в кредитах
            $credit = Credit::where([
                'user_id' => $student['id'],
                'creditable_type' => Activity::class,
                'creditable_id' => $this->activity->id,
                'coin_id' => CoinEnum::LESSON->value,
                'reason_id' => CreditReasonEnum::LESSON->value
            ])->first();

            $this->assertNotNull($credit, 'Должна быть запись о списании кредитов');
            $this->assertEquals(-1, $credit->amount, 'Должен быть списан 1 коин');

            // Проверяем баланс
            $balance = Balance::where('user_id', $student['id'])
                ->where('coin_id', CoinEnum::LESSON->value)
                ->first();
            
            $this->assertEquals(-6, $balance->amount, 'Баланс должен уменьшиться на 1');
        }
    }

    #[Test]
    public function it_handles_partial_team_balance_correctly()
    {
        // У одного студента оставляем положительный баланс, у остальных отрицательный
        $firstStudent = $this->students[0];
        foreach ($this->students as $student) {
            Balance::where('user_id', $student['id'])
                ->where('coin_id', CoinEnum::LESSON->value)
                ->update(['amount' => $student['id'] === $firstStudent['id'] ? 10 : -5]);
        }

        // Проводим активность
        $this->post(route('activities.start', $this->activity));
        $response = $this->post(route('activities.stop', $this->activity));
        
        $response->assertStatus(200);

        // Проверяем, что у всех списались коины
        foreach ($this->students as $student) {
            // Проверяем запись в кредитах
            $credit = Credit::where([
                'user_id' => $student['id'],
                'creditable_type' => Activity::class,
                'creditable_id' => $this->activity->id,
                'coin_id' => CoinEnum::LESSON->value,
                'reason_id' => CreditReasonEnum::LESSON->value
            ])->first();

            $this->assertNotNull($credit, 'Должна быть запись о списании кредитов');
            $this->assertEquals(-1, $credit->amount, 'Должен быть списан 1 коин');

            // Проверяем баланс
            $balance = Balance::where('user_id', $student['id'])
                ->where('coin_id', CoinEnum::LESSON->value)
                ->first();
            
            $expectedAmount = $student['id'] === $firstStudent['id'] ? 9 : -6;
            $this->assertEquals($expectedAmount, $balance->amount, 'Баланс должен уменьшиться на 1');
        }
    }
} 