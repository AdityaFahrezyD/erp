<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OtherExpenseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('other_expenses')->insert([
            [
                'expense_id' => Str::uuid(),
                'nama_pengeluaran' => 'Beli tinta printer',
                'keterangan' => 'Tinta printer Epson untuk cetak invoice',
                'jumlah' => 350000,
                'tanggal' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
