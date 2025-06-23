<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PosisiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('posisi')->insert([
                [
                    'posisi_id' => Str::uuid(),
                    'posisi' => 'Finance',
                    'gaji' => 4500000,
                    'tunjangan' => 450000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'posisi_id' => Str::uuid(),
                    'posisi' => 'Lead Developer',
                    'gaji' => 5000000,
                    'tunjangan' => 500000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'posisi_id' => Str::uuid(),
                    'posisi' => 'Project Leader',
                    'gaji' => 6000000,
                    'tunjangan' => 600000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'posisi_id' => Str::uuid(),
                    'posisi' => 'Staff IT',
                    'gaji' => 4000000,
                    'tunjangan' => 400000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
        ]);

        $this->command->info('Data berhasil ditambahkan!');
    }
}
