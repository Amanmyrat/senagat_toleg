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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('type');
            $table->json('user_information')->nullable();
            $table->integer('bank_id');
            $table->unsignedInteger('amount');
            $table->json('payment_target')->nullable();
            $table->string('order_id')->unique()->nullable();
            $table->string('pay_id')->nullable()->unique();
            $table->enum('status', ['pending', 'confirmed', 'failed', 'confirming'])->default('pending');
            $table->integer('error_code')->nullable();
            $table->string('error_message')->nullable();
            $table->string('return_url')->nullable();
            $table->ipAddress('client_ip')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
