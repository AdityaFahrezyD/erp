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
        Schema::create('bonuses', function (Blueprint $table) {
            $table->uuid('bonuses_id')->primary();
            $table->uuid('fk_pegawai_id');
            $table->enum('bonus_type',['performance','loyalty']);
            $table->uuid('fk_project_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->boolean('is_used')->default(false);
            $table->timestamps();

            $table->foreign('fk_pegawai_id')->references('pegawai_id')->on('pegawai');
            $table->foreign('fk_project_id')->references('project_id')->on('going_projects')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonuses');
    }
};
