<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TunjanganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
        {
            DB::table('tunjangan')->insert([
                    [
                        'tunjangan_id' => Str::uuid(),
                        'jenis_tunjangan' => 'Makan',
                        'jumlah' => 700000,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'tunjangan_id' => Str::uuid(),
                        'jenis_tunjangan' => 'Transportasi',
                        'jumlah' => 1000000,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
            ]);

            $this->command->info('Data berhasil ditambahkan!');
        }
}
