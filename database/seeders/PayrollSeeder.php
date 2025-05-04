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
                'user_id' => '8657f940-1f5e-4fae-af00-b8bf9d71f751',
                'penerima' => 'Budi Santoso',
                'keterangan' => 'Gaji bulan Maret 2025',
                'harga' => 5000000,
                'email_penerima' => 'budi@example.com',
                'tanggal_kirim' => '2025-03-30',
                'is_repeat' => false,
                'sent_at' => null,
                'approve_status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'payroll_id' => Str::uuid()->toString(),
                'user_id' => '81456209-c61f-4523-80ba-01a901345b4c',
                'penerima' => 'Siti Aminah',
                'keterangan' => 'Bonus akhir tahun',
                'harga' => 2000000,
                'email_penerima' => 'siti@example.com',
                'tanggal_kirim' => '2025-12-31',
                'is_repeat' => false,
                'sent_at' => null,
                'approve_status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
