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
            $table->string('recipient');
            $table->string('company')->nullable();
            $table->string('information');
            $table->bigInteger('invoice_amount');
            $table->string('recipient_email');
            $table->date('send_date');
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
        Schema::dropIfExists('invoice');
    }
};
