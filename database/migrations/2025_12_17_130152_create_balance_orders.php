<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('balance_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('bank_id');
            $table->decimal('amount', 12, 2);
            $table->string('phone');
            $table->string('order_id')->unique()->nullable();
            $table->string('pay_id')->nullable()->unique();
            $table->enum('status', ['pending', 'confirmed', 'failed'])->default('pending');
            $table->integer('error_code')->nullable();
            $table->string('error_message')->nullable();
            $table->string('return_url');
            $table->ipAddress('client_ip');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balance_orders');
    }
};
