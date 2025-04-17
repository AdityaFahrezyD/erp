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
            $table->uuid('finance_id', 64)->primary();
            $table->date('date');
            $table->string('description', 255);
            $table->integer('type');
            $table->integer('amount');
            $table->integer('saldo');
            $table->string('notes', 255);
            $table->boolean('status_pembayaran');
            $table->boolean('approve_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keuangan');
    }
};
