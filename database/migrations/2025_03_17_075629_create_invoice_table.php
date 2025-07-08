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
            $table->uuid('user_id');
            $table->uuid('project_id');
            $table->uuid('modul_id');
            $table->string('recipient');
            $table->string('company')->nullable();
            $table->string('information');
            $table->double('invoice_amount');
            $table->string('recipient_email');
            $table->date('send_date');
            $table->boolean('is_repeat')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->enum('approve_status', ['pending', 'approved', 'declined'])->default('pending');
            $table->timestamps();

            $table->foreign('project_id')->references('project_id')->on('going_projects')->onDelete('cascade');
            $table->foreign('modul_id')->references('id')->on('project_modul');
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
