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
            $table->uuid('fk_payroll_id')->nullable();
            $table->uuid('fk_invoice_id')->nullable();
            $table->uuid('fk_expense_id')->nullable();
            $table->enum('type', ['invoice', 'payroll', 'other']);
            $table->date('date');
            $table->decimal('amount', 15, 2); // Ubah dari integer ke decimal untuk nilai uang
            $table->decimal('saldo', 15, 2); // Ubah dari integer ke decimal untuk nilai uang
            $table->text('notes')->nullable(); // Ubah dari string ke text untuk catatan panjang
            $table->tinyInteger('status_pembayaran')->default(0); // 0=Belum Dibayar, 1=Sudah Dibayar
            $table->string('judul_transaksi')->nullable(); // Tambahan judul transaksi
            $table->timestamps();
            $table->softDeletes(); // Tambahkan soft delete jika diperlukan

            // Foreign key constraints
            $table->foreign('fk_invoice_id')->references('invoice_id')->on('invoice')->nullOnDelete();
            $table->foreign('fk_payroll_id')->references('payroll_id')->on('payroll')->nullOnDelete();
            $table->foreign('fk_expense_id')->references('expense_id')->on('other_expenses')->nullOnDelete();
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