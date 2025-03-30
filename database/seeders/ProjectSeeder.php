<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('project')->insert([
            [
                'project_id' => Str::uuid()->toString(),
                'created_by' => '1234567890123456',
                'project_name' => 'ERP Texio',
                'unpaid_amount' => '5000000',
                'status' => 'In Progress',
            ],
            [
                'project_id' => Str::uuid()->toString(),
                'created_by' => '9876543210987654',
                'project_name' => 'Sistem Absensi Asrama',
                'unpaid_amount' => '2500000',
                'status' => 'Pending',
            ],
            [
                'project_id' => Str::uuid()->toString(),
                'created_by' => '1234567890123456',
                'project_name' => 'E-Voting Ketua OSIS',
                'unpaid_amount' => '0', // Proyek sudah lunas
                'status' => 'Completed',
            ],
        ]);
    }
}
