<?php

namespace Database\Seeders;

use App\Models\Asuransi;
use App\Models\Tunjangan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            GoingProjectSeeder::class,
            InvoiceSeeder::class,
            PayrollSeeder::class,
            OtherExpenseSeeder::class,
            FinanceSeeder::class,
            PosisiSeeder::class,
            AsuransiSeeder::class,
            TunjanganSeeder::class,
        ]);

    }
}
