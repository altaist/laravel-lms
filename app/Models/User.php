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
use Illuminate\Database\Eloquent\Builder;

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
        'settings',
        'card_number',
        'card_delivered_at'
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
            'card_delivered_at' => 'datetime'
        ];
    }

    protected $appends = ['role_name'];

    protected static function booted()
    {
        static::addGlobalScope('withRole', function (Builder $builder) {
            $builder->with('role');
        });
    }

    /**
     * Scope для фильтрации только студентов
     */
    public function scopeStudents(Builder $query): Builder
    {
        return $query->where('role_id', UserRoleEnum::STUDENT);
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
     * Проверяет, является ли пользователь учеником
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

    public function loginTokens(): HasMany
    {
        return $this->hasMany(LoginToken::class);
    }

    public function createLoginToken(): string
    {
        return LoginToken::generateFor($this)->token;
    }

    public function getRoleNameAttribute(): ?string
    {
        return $this->role?->name;
    }

    /**
     * Получить все кредиты пользователя
     */
    public function credits(): HasMany
    {
        return $this->hasMany(Credit::class);
    }

    /**
     * ID, с которого начинаются обычные пользователи
     * Все ID меньше этого значения считаются системными пользователями
     */
    public const SYSTEM_USERS_MAX_ID = 100;
}
