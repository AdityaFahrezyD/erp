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
                [
                    'invoice_id' => Str::uuid(),
                    'user_id' => $userId,
                    'project_id' => $projectId,
                    'recipient' => 'John Doe',
                    'company' => 'PT Texio Digital',
                    'information' => 'Pembayaran layanan ERP Texio',
                    'invoice_amount' => 5000000,
                    'recipient_email' => 'johndoe@example.com',
                    'send_date' => now()->addDays(7),
                    'approve_status' => 'pending',
                    'is_repeat' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'invoice_id' => Str::uuid(),
                    'user_id' => $userId,
                    'project_id' => $projectId,
                    'recipient' => 'Jane Smith',
                    'company' => 'PT Mitra Digital',
                    'information' => 'Tagihan bulan Maret',
                    'invoice_amount' => 7500000,
                    'recipient_email' => 'janesmith@example.com',
                    'send_date' => now(),
                    'approve_status' => 'approved',
                    'is_repeat' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'invoice_id' => Str::uuid(),
                    'user_id' => $userId,
                    'project_id' => $projectId,
                    'recipient' => 'Michael Johnson',
                    'company' => 'PT Inovasi Digital',
                    'information' => 'Pembayaran layanan konsultasi IT',
                    'invoice_amount' => 6200000,
                    'recipient_email' => 'michael@example.com',
                    'send_date' => now(),
                    //'approve_status' => $index % 3 === 0 ? 'approved' : ($index % 2 === 0 ? 'declined' : 'pending'),
                    'approve_status' => 'declined',
                    'is_repeat' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}
