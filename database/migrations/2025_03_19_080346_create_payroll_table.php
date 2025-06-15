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
            $table->uuid('payroll_id')->primary();
            $table->uuid('user_id');
            $table->string('penerima');
            $table->string('keterangan');
            $table->integer('harga');
            $table->string('email_penerima');
            $table->date('tanggal_kirim');
            $table->boolean('is_repeat')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->enum('approve_status', ['pending', 'approved', 'declined'])->default('pending');
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
