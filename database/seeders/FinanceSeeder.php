<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        $invoice = DB::table('invoice')->first();
        $payroll = DB::table('payroll')->first();
        $other = DB::table('other_expenses')->first();

        DB::table('finance')->insert([
            [
                'finance_id' => Str::uuid(),
                'date' => now()->format('Y-m-d'),
                'description' => 'Pemasukan dari proyek A',
                'type' => 1, // 1 = Pemasukan, 0 = Pengeluaran
                'amount' => 5000000,
                'saldo' => 10000000,
                'notes' => 'Pembayaran tahap 1',
                'status_pembayaran' => true,
                'approve_status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'finance_id' => Str::uuid(),
                'date' => now()->subDays(2)->format('Y-m-d'),
                'description' => 'Pembelian perangkat keras',
                'type' => 0,
                'amount' => 2000000,
                'saldo' => 8000000,
                'notes' => 'Beli server baru',
                'status_pembayaran' => true,
                'approve_status' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'finance_id' => Str::uuid(),
                'date' => now()->subWeek()->format('Y-m-d'),
                'description' => 'Gaji karyawan bulan Maret',
                'type' => 0,
                'amount' => 3000000,
                'saldo' => 5000000,
                'notes' => 'Pembayaran gaji staff IT',
                'status_pembayaran' => false,
                'approve_status' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'finance_id' => Str::uuid(),
                'transaction_id' => $other->expense_id,
                'transaction_type' => 'other',
                'date' => now(),
                'description' => 'Pengeluaran operasional ATK',
                'amount' => $other->jumlah,
                'saldo' => 2350000,
                'notes' => 'Tinta printer',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
