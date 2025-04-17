<?php

namespace App\Filament\Widgets;

use App\Models\Finance;
use App\Models\GoingProject;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class SaldoChart extends ChartWidget
{
    protected static ?string $heading = 'Saldo and Amount per Month';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $labels = [];
        $amountData = [];
        $saldoData = [];

        // Ambil data 4 bulan terakhir
        $months = collect(range(0, 3))->map(function ($i) {
            return Carbon::now()->subMonths(3 - $i)->startOfMonth();
        });

        foreach ($months as $month) {
            $start = $month->copy();
            $end = $month->copy()->endOfMonth();

            $labels[] = $month->format('M Y');

            // Total amount dari GoingProject per bulan (unpaid_amount)
            $amount = GoingProject::whereBetween('created_at', [$start, $end])
                ->sum('unpaid_amount');

            $amountData[] = $amount;

            // Ambil saldo terakhir yang tercatat pada bulan ini
            $saldo = Finance::whereBetween('created_at', [$start, $end])
                ->orderByDesc('created_at')
                ->value('saldo') ?? 0;

            $saldoData[] = $saldo;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Amount',
                    'data' => $amountData,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.6)', // biru
                    'type' => 'bar',
                ],
                [
                    'label' => 'Saldo',
                    'data' => $saldoData,
                    'borderColor' => 'rgba(255, 99, 132, 1)', // merah
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'fill' => false,
                    'type' => 'line',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line'; // Chart campuran bar + line
    }
    public function getColumnSpan(): int | string | array
    {
        return 'full';
    }
    
}
