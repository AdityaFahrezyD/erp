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
        Schema::create('invoice', function (Blueprint $table) {
            $table->string('invoice_id', 64)->primary();
            $table->char('created_by', 16);
            $table->string('penerima', 255);
            $table->string('perusahaan', 255);
            $table->string('keterangan', 255);
            $table->integer('harga');
            $table->string('email_penerima', 255);
            $table->boolean('tipe', 255);
            $table->date('tanggal_kirim');
            $table->date('timestamp');
            $table->boolean('approve_status');

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice');
    }
};
