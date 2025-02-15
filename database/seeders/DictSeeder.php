<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Coin;
use App\Models\Reason;
use App\Enums\UserRoleEnum;
use App\Enums\CoinEnum;
use Illuminate\Database\Seeder;
use App\Enums\CreditReasonEnum;

class DictSeeder extends Seeder
{
    public function run(): void
    {
        // Очищаем таблицы
        Role::truncate();
        Coin::truncate();
        Reason::truncate();

        // Роли
        foreach (UserRoleEnum::cases() as $role) {
            Role::create([
                'id' => $role->value,
                'name' => $role->label(),
                'slug' => $role->slug(),
            ]);
        }

        // Монеты
        foreach (CoinEnum::cases() as $coin) {
            Coin::create([
                'id' => $coin->value,
                'name' => $coin->label(),
                'short_name' => $coin->shortName(),
                'code' => $coin->code(),
                'icon' => '',
                'is_virtual' => $coin->isVirtual()
            ]);
        }

        // Причины
        foreach (CreditReasonEnum::cases() as $reason) {
            Reason::create([
                'id' => $reason->value,
                'name' => $reason->getName(),
                'description' => $reason->getDescription(),
            ]);
        }
    }
} 