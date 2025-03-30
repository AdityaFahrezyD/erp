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
                'payroll_id' => Str::uuid(),
                'created_by' => '1234567890123456', // user ID
                'penerima' => 'Budi Santoso',
                'keterangan' => 'Gaji bulan Maret 2025',
                'harga' => 5000000,
                'email_penerima' => 'budi@example.com',
                'tipe' => true, // Misal 1 = Gaji, 0 = Bonus
                'tanggal_kirim' => '2025-03-30',
                'timestamp' => now(),
                'approve_status' => true, // true = Disetujui, false = Ditolak
            ],
            [
                'payroll_id' => Str::uuid(),
                'created_by' => '9876543210987654',
                'penerima' => 'Siti Aminah',
                'keterangan' => 'Bonus akhir tahun',
                'harga' => 2000000,
                'email_penerima' => 'siti@example.com',
                'tipe' => false,
                'tanggal_kirim' => '2025-12-31',
                'timestamp' => now(),
                'approve_status' => true,
            ]
        ]);
    }
}
