<?php

namespace Database\Seeders;

use App\Models\Coin;
use Illuminate\Database\Seeder;

class CoinSeeder extends Seeder
{
    public function run()
    {
        Coin::create([
            'id' => 1,
            'name' => 'Рубль',
            'shor_tname' => 'р',
            'code' => 'rub',
            'icon' => '',
            'is_virtual' => false
        ]);

        Coin::create([
            'id' => 2,
            'name' => 'Занятие',
            'short_name' => 'зан',
            'code' => 'lesson',
            'icon' => '',
            'is_virtual' => true
        ]);

        Coin::create([
            'id' => 3,
            'name' => 'Майнкрафтик',
            'short_name' => 'майн',
            'code' => 'mine',
            'icon' => '',
            'is_virtual' => true
        ]);

    }
} 