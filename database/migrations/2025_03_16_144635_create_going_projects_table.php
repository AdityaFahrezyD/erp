<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tabel going_projects
        Schema::create('going_projects', function (Blueprint $table) {
            $table->uuid('project_id')->primary();
            $table->string('project_name');
            $table->double('total_harga_proyek')->nullable();
            $table->double('unpaid_amount')->nullable()->default(0);
            $table->enum('status', ['pending','on progress', 'done', 'cancelled', 'waiting for payment'])->default('on progress');
            $table->date('batas_awal')->nullable();
            $table->date('batas_akhir')->nullable();
            $table->double('harga_awal')->nullable();
            $table->string('company');
            $table->string('pic');
            $table->string('pic_email');
            $table->uuid('project_leader');
            $table->timestamps();

            $table->foreign('project_leader')->references('pegawai_id')->on('pegawai')->onDelete('cascade');
        });

        // Tabel project_modul
        Schema::create('project_modul', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->string('nama_modul', 50);
            $table->string('deskripsi_modul', 200) ->nullable();
            $table->integer('alokasi_dana');
            $table->double('unpaid_amount')->nullable()->default(0);
            $table->enum('status', ['new', 'on progress', 'done'])->default('new');
            $table->timestamps();

            $table->foreign('project_id')->references('project_id')->on('going_projects')->onDelete('cascade');
        });

            Schema::create('sub_modul', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('modul_id');
            $table->date('batas_awal')->nullable();
            $table->date('batas_akhir')->nullable();
            $table->string('nama_sub_modul', 50);
            $table->string('deskripsi_sub_modul', 200) ->nullable();
            $table->enum('status', ['new', 'on progress', 'done'])->default('new');
            $table->integer('optimistic_time')->nullable();        
            $table->integer('most_likely_time')->nullable(); 
            $table->integer('pessimistic_time')->nullable(); 

            $table->double('est')->nullable();
            $table->double('eft')->nullable();
            $table->double('lst')->nullable();
            $table->double('lft')->nullable();
            $table->double('total_float')->nullable();
            $table->double('expected_time')->nullable(); 
            $table->double('variance')->nullable();         

            $table->boolean('is_critical_path')->default(false);
            $table->timestamps();

            $table->foreign('modul_id')->references('id')->on('project_modul')->onDelete('cascade');
        });

        Schema::create('sub_modul_dependencies', function (Blueprint $table) {
            $table->uuid('sub_modul_id');
            $table->uuid('depends_on_sub_modul_id');
            $table->timestamps();

            $table->primary(['sub_modul_id', 'depends_on_sub_modul_id']);

            $table->foreign('sub_modul_id')->references('id')->on('sub_modul')->onDelete('cascade');
            $table->foreign('depends_on_sub_modul_id')->references('id')->on('sub_modul')->onDelete('cascade');
        });


        // Tabel project_staff
        Schema::create('project_staff', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_pegawai');
            $table->uuid('sub_modul_id');
            $table->enum('status', ['new', 'on progress', 'ready for test', 'done'])->default('new');
            $table->timestamps();

            $table->foreign('id_pegawai')->references('pegawai_id')->on('pegawai')->onDelete('cascade');
            $table->foreign('sub_modul_id')->references('id')->on('sub_modul')->onDelete('cascade');
        });

        // Tabel project_leader_staff
        // Schema::create('project_leader_staff', function (Blueprint $table) {
        //     $table->uuid('id')->primary();
        //     $table->uuid('id_user');
        //     $table->uuid('modul_id');
        //     $table->enum('status', ['new', 'on progress', 'ready for test', 'done'])->default('new');
        //     $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
        //     $table->foreign('modul_id')->references('id')->on('project_modul')->onDelete('cascade');
        //     $table->timestamps();
        // });

    }

    public function down(): void
    {
        Schema::dropIfExists('project_staff');
        Schema::dropIfExists('project_payment');
        Schema::dropIfExists('sub_modul_dependencies');
        Schema::dropIfExists('sub_modul');
        Schema::dropIfExists('project_modul');
        Schema::dropIfExists('going_projects');
    }
};
