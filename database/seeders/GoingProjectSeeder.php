<?php

namespace Database\Seeders;

use App\Models\GoingProject;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GoingProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $values = [
            [
                'project_id' => Str::uuid()->toString(),
                'project_name' => 'Soundbar',
                'unpaid_amount' => 52000000,
                'status' => 'In Progress',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'project_id' => Str::uuid()->toString(),
                'project_name' => 'Svarga',
                'unpaid_amount' => 132000000,
                'status' => 'Completed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($values as $value) {
            DB::table('going_projects')->insert($value);
        }
    }
}
