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
            ['project_name' => 'Soundbar', 'unpaid_amount' => 52000000],
            ['project_name' => 'Svarga', 'unpaid_amount' => 132000000],
        ];

        foreach ($values as $value) {
            GoingProject::create($value);
        }
    }
}
