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
            $table->uuid('fk_pegawai_id');
            $table->decimal('gross_salary', 15, 2); //Gaji kotor untuk periode tersebut.
            $table->decimal('net_salary', 15, 2); //Gaji bersih setelah pemotongan dan bonus.
            $table->string('email_penerima');
            $table->date('tanggal_kirim');
            $table->boolean('adjustment')->default(false);
            $table->text('adjustment_desc')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->enum('approve_status', ['pending', 'approved', 'declined'])->default('pending');
            $table->timestamps();

            $table->foreign('fk_pegawai_id')->references('pegawai_id')->on('pegawai');
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
