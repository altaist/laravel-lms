<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('coins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name');
            $table->string('code');
            $table->string('icon')->nullable();
            $table->boolean('is_virtual')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('coins');
    }
};