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
        Schema::table('users', function (Blueprint $table) {
            $table->char('id', 16)->change();
            $table->string('first_name', 50)->after('email_verified_at');
            $table->string('last_name', 50)->after('email_verified_at');
            $table->char('images', 255)->after('email');
            $table->string('role')->after('images');
            $table->char('created_by', 16)->after('updated_at');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
