<?php

namespace App\Filament\Widgets;

use App\Models\Finance;
use App\Models\GoingProject;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InfoWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Saldo terakhir berdasarkan total kumulatif dari transaksi lunas
        $latestSaldo = Finance::where('status_pembayaran', 1)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->value('saldo') ?? 0;

        // Total piutang (dari proyek yang belum dibayar)
        $totalPiutang = GoingProject::whereIn('status', ['on progress', 'waiting for payment', 'pending'])
            ->sum('unpaid_amount') ?? 0;

        // Total saldo + piutang
        $expectedSaldo = $latestSaldo + $totalPiutang;

        // Pengeluaran bulanan (misalnya 10 juta)
        $monthlyExpense = 10000000;
        $runway = $monthlyExpense > 0 ? floor($latestSaldo / $monthlyExpense) : 0;
        $expectedRunway = $monthlyExpense > 0 ? floor($expectedSaldo / $monthlyExpense) : 0;

        // Jumlah Going Project yang aktif
        $totalGoingProject = GoingProject::whereIn('status', ['on progress', 'waiting for payment'])->count();

        return [
            Stat::make('Total Saldo', 'Rp. ' . number_format($latestSaldo, 0, ',', '.')),
            Stat::make('Total Piutang', 'Rp. ' . number_format($totalPiutang, 0, ',', '.')),
            Stat::make('Expected Saldo', 'Rp. ' . number_format($expectedSaldo, 0, ',', '.')),
            Stat::make('Runway Saat Ini', $runway . ' bulan'),
            Stat::make('Expected Runway', $expectedRunway . ' bulan'),
            Stat::make('Total Going Project', $totalGoingProject . ' proyek'),
        ];
    }
}