<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan user sudah ada sebelum membuat referensi
        $users = DB::table('users')->get();
        
        if ($users->isEmpty()) {
            $this->command->error('Tidak ada user yang tersedia. Jalankan UserSeeder terlebih dahulu!');
            return;
        }
        
        // Dapatkan ID user untuk foreign key (menggunakan UUID)
        // Gunakan user pertama jika user yang dicari tidak ditemukan
        $adminId = DB::table('users')->where('role', 'admin')->first()?->id ?? $users->first()->id;
        $financeId = DB::table('users')->where('role', 'finance')->first()?->id ?? $users->first()->id;
        $ownerId = DB::table('users')->where('role', 'owner')->first()?->id ?? $users->first()->id;
        $staffId = DB::table('users')->where('role', 'staff')->first()?->id ?? $users->first()->id;

        // Hapus pengecekan tabel lain yang tidak ada (invoice, payroll, other)
        // dan gunakan nilai langsung
        
        // Hitung tanggal saat ini untuk data
        $now = now();

        // Buat array data keuangan
        $financesData = [
            [
                'finance_id' => Str::uuid()->toString(),
                'transaction_id' => 'TRX-' . strtoupper(Str::random(8)),
                'user_id' => $adminId,
                'date' => $now->format('Y-m-d'),
                'description' => 'Pemasukan dari proyek A',
                'type' => 'invoice',
                'amount' => 5000000.00,
                'saldo' => 5000000.00,
                'notes' => 'Pembayaran tahap 1',
                'status_pembayaran' => 1, // Sudah Dibayar
                'approve_status' => 1, // Disetujui
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'finance_id' => Str::uuid()->toString(),
                'transaction_id' => 'TRX-' . strtoupper(Str::random(8)),
                'user_id' => $financeId,
                'date' => $now->subDays(2)->format('Y-m-d'),
                'description' => 'Pembelian perangkat keras',
                'type' => 'other',
                'amount' => -2000000.00,
                'saldo' => 3000000.00,
                'notes' => 'Beli server baru',
                'status_pembayaran' => 1,
                'approve_status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'finance_id' => Str::uuid()->toString(),
                'transaction_id' => 'TRX-' . strtoupper(Str::random(8)),
                'user_id' => $ownerId,
                'date' => $now->subDays(5)->format('Y-m-d'),
                'description' => 'Gaji karyawan bulan Maret',
                'type' => 'payroll',
                'amount' => -3000000.00,
                'saldo' => 0.00,
                'notes' => 'Pembayaran gaji staff IT',
                'status_pembayaran' => 0,
                'approve_status' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'finance_id' => Str::uuid()->toString(),
                'transaction_id' => 'TRX-' . strtoupper(Str::random(8)),
                'user_id' => $staffId,
                'date' => $now->format('Y-m-d'),
                'description' => 'Pengeluaran operasional ATK',
                'type' => 'other',
                'amount' => -350000.00,
                'saldo' => 0.00,
                'notes' => 'Tinta printer',
                'status_pembayaran' => 0,
                'approve_status' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'finance_id' => Str::uuid()->toString(),
                'transaction_id' => 'TRX-' . strtoupper(Str::random(8)),
                'user_id' => $staffId,
                'date' => $now->addDay()->format('Y-m-d'),
                'description' => 'Penawaran Proyek Website',
                'type' => 'invoice',
                'amount' => 8500000.00,
                'saldo' => 0.00,
                'notes' => 'Proposal website e-commerce',
                'status_pembayaran' => 0,
                'approve_status' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        // Insert data ke tabel finances
        DB::table('finances')->insert($financesData);
        
        $this->command->info('Data keuangan berhasil ditambahkan!');
    }
}