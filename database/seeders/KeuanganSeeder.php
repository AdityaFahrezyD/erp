<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KeuanganSeeder extends Seeder
{
    public function run()
    {
        DB::table('keuangan')->insert([
            [
                'keuangan_id' => Str::uuid()->toString(),
                'created_by' => '1234567890123456', // Sesuai user_id yang ada di users
                'date' => now()->format('Y-m-d'),
                'description' => 'Pemasukan dari proyek A',
                'type' => 1, // 1 = pemasukan, 0 = pengeluaran
                'amount' => 5000000,
                'saldo' => 10000000,
                'notes' => 'Pembayaran tahap 1',
                'status_pembayaran' => true,
                'approve_status' => true,
            ],
            [
                'keuangan_id' => Str::uuid()->toString(),
                'created_by' => '1234567890123456',
                'date' => now()->subDays(2)->format('Y-m-d'),
                'description' => 'Pembelian perangkat keras',
                'type' => 0, // Pengeluaran
                'amount' => 2000000,
                'saldo' => 8000000,
                'notes' => 'Beli server baru',
                'status_pembayaran' => true,
                'approve_status' => false,
            ],
            [
                'keuangan_id' => Str::uuid()->toString(),
                'created_by' => '1234567890123456',
                'date' => now()->subWeek()->format('Y-m-d'),
                'description' => 'Gaji karyawan bulan Maret',
                'type' => 0,
                'amount' => 3000000,
                'saldo' => 5000000,
                'noted' => 'Pembayaran gaji staff IT',
                'status_pembayaran' => false,
                'approve_status' => false,
            ]
        ]);
    }
}
