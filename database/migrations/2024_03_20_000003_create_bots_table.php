<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_id')->constrained('socials')->onDelete('cascade');
            $table->string('name');
            $table->string('type')->default('telegram'); // telegram, vk, whatsapp и т.д.
            $table->string('token')->unique();
            $table->string('username')->nullable(); // @username бота
            $table->string('webhook_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('webhook_enabled')->default(true);
            $table->json('commands')->nullable(); // Список доступных команд бота
            $table->json('settings')->nullable(); // Дополнительные настройки
            $table->json('menu_settings')->nullable(); // Настройки меню бота
            $table->text('description')->nullable(); // Описание бота
            $table->string('about')->nullable(); // Краткое описание
            $table->string('avatar')->nullable(); // URL аватара бота
            $table->timestamps();
            $table->softDeletes();

            // Индексы для оптимизации запросов
            $table->index('type');
            $table->index('is_active');
            $table->index('webhook_enabled');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bots');
    }
}; 