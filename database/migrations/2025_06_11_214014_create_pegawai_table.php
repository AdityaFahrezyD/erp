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
        Schema::create('pegawai', function (Blueprint $table) {
            $table->uuid('pegawai_id')->primary();
            $table->string('nama');
            $table->string('position');
            $table->date('start_date');
            $table->string('email');
            $table->string('phone');
            $table->decimal('base_salary',15,2);
            $table->enum('pay_cycle', ['monthly', 'weekly'])->default('monthly');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};
