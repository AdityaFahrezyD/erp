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
        // Menggunakan UUID sebagai primary key sesuai dengan schema tabel users
        DB::table('users')->insert([
            [
                'id' => Str::uuid()->toString(), // UUID sebagai primary key
                'email' => 'admin@example.com',
                'name' => 'Admin Texio',
                'first_name' => 'Admin',
                'last_name' => 'Texio',
                'password' => Hash::make('admin123'),
                'image' => 'default.png',
                'role' => 'admin', // admin, owner, finance, staff
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'email' => 'user@example.com',
                'name' => 'Kurnia Agusta',
                'first_name' => 'Kurnia',
                'last_name' => 'Agusta',
                'password' => Hash::make('Agus123'),
                'image' => 'default.png',
                'role' => 'finance',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'email' => 'jane.doe@example.com',
                'name' => 'Jane Doe',
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'password' => Hash::make('Jane123'),
                'image' => 'default.png',
                'role' => 'owner',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'email' => 'regular@example.com',
                'name' => 'Regular User',
                'first_name' => 'Regular',
                'last_name' => 'User',
                'password' => Hash::make('user123'),
                'image' => 'default.png',
                'role' => 'staff', // Mengganti 'user' ke 'staff' sesuai dengan enum di migrasi
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->command->info('Data user berhasil ditambahkan!');
    }
}