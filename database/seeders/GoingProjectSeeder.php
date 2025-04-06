<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GoingProjectSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('going_projects')->insert([
            [
                'project_id' => Str::uuid(),
                'project_name' => 'Pembuatan Sistem Inventory',
                'unpaid_amount' => 10000000,
                'status' => 'on progress',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'project_id' => Str::uuid(),
                'project_name' => 'Pembuatan Sistem Inventory',
                'unpaid_amount' => 20000000,
                'status' => 'on progress',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
