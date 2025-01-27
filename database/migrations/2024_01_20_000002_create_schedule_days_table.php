<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('schedule_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')
                ->constrained()
                ->onDelete('cascade');
            $table->tinyInteger('day_of_week'); // 1-7 для дней недели
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            // Индекс для быстрого поиска по дню недели
            $table->index(['schedule_id', 'day_of_week']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('schedule_days');
    }
}; 