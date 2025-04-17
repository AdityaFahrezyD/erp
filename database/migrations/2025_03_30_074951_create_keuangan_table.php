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
        Schema::create('finance', function (Blueprint $table) {
            $table->uuid('finance_id')->primary();
            $table->uuid('transaction_id');
            $table->enum('transaction_type', ['invoice', 'payroll', 'other']);
            $table->date('date');
            $table->string('description');
            $table->integer('amount');
            $table->integer('saldo');
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance');
    }
};
