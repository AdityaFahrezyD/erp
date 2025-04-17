<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        $other = DB::table('other_expenses')->first();

        DB::table('finance')->insert([
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
