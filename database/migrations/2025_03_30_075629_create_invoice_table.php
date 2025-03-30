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
            $table->uuid('invoice_id')->primary();
            $table->uuid('users_id');
            $table->uuid('project_id');
            $table->string('penerima');
            $table->string('perusahaan');
            $table->string('keterangan');
            $table->integer('harga');
            $table->string('email_penerima');
            $table->date('tanggal_kirim');
            $table->boolean('approve_status')->default(false);
            $table->timestamps();
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
