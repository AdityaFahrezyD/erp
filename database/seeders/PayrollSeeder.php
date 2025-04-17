<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PayrollSeeder extends Seeder
{
    public function run()
    {
        DB::table('payroll')->insert([
            [
                'payroll_id' => Str::uuid()->toString(),
                'penerima' => 'Budi Santoso',
                'keterangan' => 'Gaji bulan Maret 2025',
                'harga' => 5000000,
                'email_penerima' => 'budi@example.com',
                'tanggal_kirim' => '2025-03-30',
                'approve_status' => true, // true = Disetujui, false = Ditolak
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'payroll_id' => Str::uuid()->toString(),
                'penerima' => 'Siti Aminah',
                'keterangan' => 'Bonus akhir tahun',
                'harga' => 2000000,
                'email_penerima' => 'siti@example.com',
                'tanggal_kirim' => '2025-12-31',
                'approve_status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
