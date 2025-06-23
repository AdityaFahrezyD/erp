<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AsuransiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
        public function run()
        {
            DB::table('asuransi')->insert([
                    [
                        'asuransi_id' => Str::uuid(),
                        'tingkat' => 'Kelas 1',
                        'iuran' => 150000,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'asuransi_id' => Str::uuid(),
                        'tingkat' => 'Kelas 2',
                        'iuran' => 100000,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'asuransi_id' => Str::uuid(),
                        'tingkat' => 'Kelas 3',
                        'iuran' => 35000,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
            ]);

            $this->command->info('Data berhasil ditambahkan!');
        }
    
}
