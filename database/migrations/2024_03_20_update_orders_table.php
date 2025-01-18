<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained();
            $table->foreignId('company_id')->nullable()->constrained();
            $table->foreignId('shipping_address_id')->constrained('addresses');
            $table->foreignId('billing_address_id')->constrained('addresses');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['company_id']);
            $table->dropForeign(['shipping_address_id']);
            $table->dropForeign(['billing_address_id']);
            $table->dropColumn(['user_id', 'company_id', 'shipping_address_id', 'billing_address_id']);
        });
    }
}; 