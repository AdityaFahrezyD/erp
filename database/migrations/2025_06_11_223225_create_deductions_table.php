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
        Schema::create('deductions', function (Blueprint $table) {
            $table->uuid('deduction_id')->primary();
            $table->uuid('fk_pegawai_id');
            $table->enum('deduction_type', ['penalty'])->default('penalty');
            $table->decimal('amount', 15, 2);
            $table->boolean('is_used')->default(false);
            $table->timestamps();

            $table->foreign('fk_pegawai_id')->references('pegawai_id')->on('pegawai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deductions');
    }
};
