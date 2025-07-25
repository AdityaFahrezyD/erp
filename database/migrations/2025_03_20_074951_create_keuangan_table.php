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
            $table->id(); 
            $table->uuid('finance_id')->unique();
            $table->string('transaction_id')->unique();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->uuid('fk_payroll_id')->nullable();
            $table->uuid('fk_invoice_id')->nullable();
            $table->uuid('fk_expense_id')->nullable();
            $table->enum('type', ['invoice', 'payroll', 'other']);
            $table->date('date');
            $table->decimal('amount', 15, 2); 
            $table->decimal('saldo', 15, 2); 
            $table->text('notes')->nullable(); 
            $table->tinyInteger('status_pembayaran')->default(0); 
            $table->string('judul_transaksi')->nullable(); 
            $table->timestamps();
            $table->softDeletes(); 


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