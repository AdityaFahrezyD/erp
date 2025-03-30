<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'id' => '1234567890123456',
                'email' => 'admin@example.com',
                'name' => 'Admin Texio',
                'first_name' => 'Admin',
                'last_name' => 'Texio',
                'password' => Hash::make('admin123'),
                'images' => 'default.png',
                'role' => 1, // 1 = Admin, 2 = User, dll.
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => 'system',
            ],
            [
                'id' => '9876543210987654',
                'email' => 'user@example.com',
                'name' => 'Kurnia Agusta',
                'first_name' => 'Kurnia',
                'last_name' => 'Agusta',
                'password' => Hash::make('Agus123'),
                'images' => 'default.png',
                'role' => 2,
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => 'system',
            ],
        ]);
    }
}
