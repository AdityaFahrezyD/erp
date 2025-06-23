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
        Schema::create('other_expenses', function (Blueprint $table) {
            $table->uuid('expense_id')->primary();
            $table->uuid('user_id');
            $table->enum('type_expense', ['project', 'other']);
            $table->uuid('fk_project_id')->nullable();
            $table->enum('name',['transport', 'accommodation', 'consultant', 'printing', 'equipment', 'entertainment','vacation','tax']);
            $table->string('judul_project')->nullable();
            $table->uuid('project_staff_id')->nullable();
            $table->string('nama_pengeluaran');
            $table->string('keterangan');
            $table->decimal('jumlah', 15, 2);
            $table->date('tanggal');
            $table->enum('approve_status', ['pending', 'approved', 'declined'])->default('pending');
            $table->timestamps();

            $table->foreign('fk_project_id')->references('project_id')->on('going_projects')->nullOnDelete();
            $table->foreign('project_staff_id')->references('id')->on('project_staff')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('other_expenses');
    }
};
