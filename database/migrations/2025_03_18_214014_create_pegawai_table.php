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
            $table->uuid('fk_posisi_id');
            $table->date('start_date');
            $table->string('email');
            $table->string('phone');
            $table->enum('status', ['maried', 'single'])->default('single');
            $table->integer('tanggungan');
            $table->uuid('fk_asuransi_id');
            $table->decimal('base_salary',15,2);
            $table->enum('pay_cycle', ['monthly', 'weekly'])->default('monthly');
            $table->timestamps();

            $table->foreign('fk_posisi_id')->references('posisi_id')->on('posisi');
            $table->foreign('fk_asuransi_id')->references('asuransi_id')->on('asuransi');
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
