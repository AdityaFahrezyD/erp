<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('invoice')->insert([
            [
                'invoice_id' => Str::uuid(),
                'created_by' => Str::random(16),
                'penerima' => 'John Doe',
                'perusahaan' => 'PT Texio Digital',
                'keterangan' => 'Pembayaran layanan ERP Texio',
                'harga' => 5000000,
                'email_penerima' => 'johndoe@example.com',
                'tipe' => true,
                'tanggal_kirim' => now(),
                'approve_status' => true, // True = Disetujui, False = Ditolak
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'invoice_id' => Str::uuid(),
                'created_by' => Str::random(16),
                'penerima' => 'Jane Smith',
                'perusahaan' => 'PT Mitra Digital',
                'keterangan' => 'Tagihan bulan Maret',
                'harga' => 7500000,
                'email_penerima' => 'janesmith@example.com',
                'tipe' => false,
                'tanggal_kirim' => now(),
                'approve_status' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'invoice_id' => Str::uuid(),
                'created_by' => Str::random(16),
                'penerima' => 'Michael Johnson',
                'perusahaan' => 'PT Inovasi Digital',
                'keterangan' => 'Pembayaran layanan konsultasi IT',
                'harga' => 6200000,
                'email_penerima' => 'michael@example.com',
                'tipe' => true,
                'tanggal_kirim' => now(),
                'approve_status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
