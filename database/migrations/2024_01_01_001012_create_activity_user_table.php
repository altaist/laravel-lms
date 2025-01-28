<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activity_user', function (Blueprint $table) {
            $table->foreignId('activity_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('attached_at');
            $table->primary(['activity_id', 'user_id']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_user');
    }
}; 