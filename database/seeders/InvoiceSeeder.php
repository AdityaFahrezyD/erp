<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $projectId = DB::table('going_projects')->first()->project_id;

        foreach (range(1, 20) as $index) {
            $userId = User::inRandomOrder()->first()->id;

            DB::table('invoice')->insert([
                'invoice_id' => Str::uuid(),
                'project_id' => $projectId,
                'penerima' => 'John Doe',
                'perusahaan' => 'PT Texio Digital',
                'keterangan' => 'Pembayaran layanan ERP Texio',
                'harga' => 5000000,
                'email_penerima' => 'johndoe@example.com',
                'tanggal_kirim' => now(),
                'approve_status' => true, // True = Disetujui, False = Ditolak
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'invoice_id' => Str::uuid(),
                'project_id' => $projectId,
                'penerima' => 'Jane Smith',
                'perusahaan' => 'PT Mitra Digital',
                'keterangan' => 'Tagihan bulan Maret',
                'harga' => 7500000,
                'email_penerima' => 'janesmith@example.com',
                'tanggal_kirim' => now(),
                'approve_status' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'invoice_id' => Str::uuid(),
                'project_id' => $projectId,
                'penerima' => 'Michael Johnson',
                'perusahaan' => 'PT Inovasi Digital',
                'keterangan' => 'Pembayaran layanan konsultasi IT',
                'harga' => 6200000,
                'email_penerima' => 'michael@example.com',
                'tanggal_kirim' => now(),
                'approve_status' => $index % 2 == 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
