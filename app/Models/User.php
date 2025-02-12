<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Balance;
use App\Enums\UserRoleEnum;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'key',
        'role_id',
        'parent_id',
        'status',
        'person',
        'statistic',
        'settings'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'person' => 'object',
            'statistic' => 'object',
            'settings' => 'object',
            'role_id' => UserRoleEnum::class,
        ];
    }

    public function activities()
    {
        return $this->belongsToMany(Activity::class)
            ->withPivot('attached_at');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class)
            ->withTimestamps();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function authoredPayments()
    {
        return $this->hasMany(Payment::class, 'author_id');
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'user_tasks')
            ->withPivot(['answer', 'result'])
            ->withTimestamps();
    }

    /**
     * Получить балансы пользователя
     */
    public function balances(): HasMany
    {
        return $this->hasMany(Balance::class);
    }

    /**
     * Получить роль пользователя
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Проверяет, является ли пользователь учителем
     */
    public function isTeacher(): bool
    {
        return $this->role_id === UserRoleEnum::TEACHER;
    }

    /**
     * Проверяет, является ли пользователь администратором
     */
    public function isAdmin(): bool
    {
        return $this->role_id === UserRoleEnum::ADMIN;
    }

    /**
     * Проверяет, является ли пользователь студентом
     */
    public function isStudent(): bool
    {
        return $this->role_id === UserRoleEnum::STUDENT;
    }

    /**
     * Проверяет, является ли пользователь методистом
     */
    public function isMethodist(): bool
    {
        return $this->role_id === UserRoleEnum::METHODIST;
    }

    /**
     * Получить домашний маршрут пользователя на основе его роли
     */
    public function getHomeRoute(): string
    {
        return match($this->role_id) {
            UserRoleEnum::TEACHER => 'lk.teacher',
            UserRoleEnum::STUDENT => 'lk.student',
            UserRoleEnum::METHODIST => 'lk.methodist',
            UserRoleEnum::ADMIN => 'admin.dashboard',
            default => 'dashboard',
        };
    }
}
