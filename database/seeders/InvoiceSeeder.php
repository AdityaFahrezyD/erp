<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceSeeder extends Seeder
{
    public function run()
    {
        DB::table('invoice')->insert([
            [
                'invoice_id' => Str::uuid()->toString(),
                'created_by' => '1234567890123456', // ID user
                'penerima' => 'John Doe',
                'perusahaan' => 'PT Texio Digital',
                'keterangan' => 'Pembayaran layanan ERP Texio',
                'harga' => 5000000,
                'email_penerima' => 'johndoe@example.com',
                'tipe' => true, // Misal: true untuk kredit, false untuk debit
                'tanggal_kirim' => now(),
                'timestamp' => now(),
                'approve_status' => true, // Misal: true = disetujui, false = ditolak
            ],
            [
                'invoice_id' => Str::uuid()->toString(),
                'created_by' => '9876543210987654',
                'penerima' => 'Jane Smith',
                'perusahaan' => 'PT Mitra Digital',
                'keterangan' => 'Tagihan bulan Maret',
                'harga' => 7500000,
                'email_penerima' => 'janesmith@example.com',
                'tipe' => false,
                'tanggal_kirim' => now(),
                'timestamp' => now(),
                'approve_status' => false,
            ],
        ]);
    }
}
