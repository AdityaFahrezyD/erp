<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'id' => Str::uuid(),
                'email' => 'admin@example.com',
                'name' => 'Admin Texio',
                'first_name' => 'Admin',
                'last_name' => 'Texio',
                'password' => Hash::make('admin123'),
                'images' => 'default.png',
                'role' => '1', // 1 = Admin, 2 = User, dll.
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => Str::random(16),
            ],
            [
                'id' => Str::uuid(),
                'email' => 'user@example.com',
                'name' => 'Kurnia Agusta',
                'first_name' => 'Kurnia',
                'last_name' => 'Agusta',
                'password' => Hash::make('Agus123'),
                'images' => 'default.png',
                'role' => '2', // 1 = Admin, 2 = User, dll.
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => Str::random(16),
            ],
            [
                'id' => Str::uuid(),
                'email' => 'jane.doe@example.com',
                'name' => 'Jane Doe',
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'password' => Hash::make('Jane123'),
                'images' => 'default.png',
                'role' => '2', // 1 = Admin, 2 = User, dll.
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => Str::random(16),
            ],
        ]);
    }
}
