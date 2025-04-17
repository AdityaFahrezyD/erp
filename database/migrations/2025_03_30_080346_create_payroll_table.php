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
        Schema::create('payroll', function (Blueprint $table) {
            $table->uuid('payroll_id', 64)->primary();
            $table->char('created_by', 16);
            $table->string('penerima', 255);
            $table->string('keterangan', 255);
            $table->integer('harga');
            $table->string('email_penerima', 255);
            $table->boolean('tipe', 255);
            $table->date('tanggal_kirim');
            $table->boolean('approve_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll');
    }
};
