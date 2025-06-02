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
        Schema::create('finances', function (Blueprint $table) {
            $table->id(); // Tambahkan auto-increment ID 
            $table->uuid('finance_id')->unique(); // Ubah dari primary ke unique
            $table->string('transaction_id')->unique(); // Ubah dari uuid ke string
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->enum('type', ['invoice', 'payroll', 'other']);
            $table->date('date');
            $table->string('description');
            $table->decimal('amount', 15, 2); // Ubah dari integer ke decimal untuk nilai uang
            $table->decimal('saldo', 15, 2); // Ubah dari integer ke decimal untuk nilai uang
            $table->text('notes')->nullable(); // Ubah dari string ke text untuk catatan panjang
            $table->tinyInteger('status_pembayaran')->default(0); // 0=Belum Dibayar, 1=Sudah Dibayar
            $table->tinyInteger('approve_status')->default(0); // 0=Menunggu, 1=Disetujui, 2=Ditolak
            $table->timestamps();
            $table->softDeletes(); // Tambahkan soft delete jika diperlukan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finances');
    }
};