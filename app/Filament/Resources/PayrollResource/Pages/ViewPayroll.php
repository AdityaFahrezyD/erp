<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Resources\PayrollResource;
use Filament\Resources\Pages\Page;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use App\Models\Bonuses;
use App\Models\Deductions;
use App\Models\Payroll;
use Carbon\Carbon;

class ViewPayroll extends Page
{
    protected static string $resource = PayrollResource::class;

    protected static string $view = 'payroll-resource.page.view-payroll';

    protected static ?string $title = 'Detail Payroll';

    public function mount($record = null): void
    {
        if (!$record || !$this->record = Payroll::find($record)) {
            abort(404, 'Payroll record not found.');
        }
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $record = $this->record;
        $gross_salary = $record->gross_salary;
        $total_bonuses = Bonuses::where('fk_pegawai_id', $record->fk_pegawai_id)->sum('amount') ?? 0;
        $total_deductions = Deductions::where('fk_pegawai_id', $record->fk_pegawai_id)->sum('amount') ?? 0;
        $tunjangan_mkn = 700000; // Tunjangan makan tetap
        $tunjangan_trns = 1000000; // Tunjangan transportasi tetap
        $asuransi = $record->pegawai->asuransi->iuran ?? 0;
        $tunjangan = $record->pegawai->posisi->tunjangan ?? 0;

        // Hitung pajak untuk gaji pokok
        $pajak = 0;
        if ($record->jenis_gaji === 'gaji_pokok') {
            $penghasilan_bulanan = $gross_salary + $total_bonuses + $tunjangan_mkn + $tunjangan_trns - $total_deductions - $asuransi;
            $penghasilan_tahunan = $penghasilan_bulanan * 12;

            $statusPajak = match (true) {
                $record->pegawai->status === 'single' => 'TK0',
                $record->pegawai->status === 'married' && $record->pegawai->tanggungan === 0 => 'K0',
                $record->pegawai->status === 'married' && $record->pegawai->tanggungan === 1 => 'K1',
                $record->pegawai->status === 'married' && $record->pegawai->tanggungan === 2 => 'K2',
                $record->pegawai->status === 'married' && $record->pegawai->tanggungan >= 3 => 'K3',
                default => 'TK0',
            };

            $ptkp = match ($statusPajak) {
                'TK0' => 54000000,
                'K0' => 58500000,
                'K1' => 63000000,
                'K2' => 67500000,
                'K3' => 72000000,
                default => 54000000,
            };

            $pkp = max(0, $penghasilan_tahunan - $ptkp);
            $pajak_tahunan = 0;
            $sisa_pkp = $pkp;

            if ($sisa_pkp > 0) {
                $lapisan1 = min($sisa_pkp, 60000000);
                $pajak_tahunan += $lapisan1 * 0.05;
                $sisa_pkp -= $lapisan1;
            }
            if ($sisa_pkp > 0) {
                $lapisan2 = min($sisa_pkp, 190000000);
                $pajak_tahunan += $lapisan2 * 0.15;
                $sisa_pkp -= $lapisan2;
            }
            if ($sisa_pkp > 0) {
                $lapisan3 = min($sisa_pkp, 250000000);
                $pajak_tahunan += $lapisan3 * 0.25;
                $sisa_pkp -= $lapisan3;
            }
            if ($sisa_pkp > 0) {
                $lapisan4 = min($sisa_pkp, 4500000000);
                $pajak_tahunan += $lapisan4 * 0.30;
                $sisa_pkp -= $lapisan4;
            }
            if ($sisa_pkp > 0) {
                $pajak_tahunan += $sisa_pkp * 0.35;
            }

            $pajak = $pajak_tahunan / 12;
        }

        // Hitung masa kerja untuk THR
        $masa_kerja_bulan = 0;
        if ($record->jenis_gaji === 'thr') {
            $start_date = Carbon::parse($record->pegawai->start_date);
            $masa_kerja_bulan = max(0, (int) $start_date->diffInMonths(now()));
        }

        return $infolist
            ->record($record)
            ->schema([
                Section::make('Informasi Payroll')
                    ->schema([
                        TextEntry::make('pegawai.nama')
                            ->label('Nama Pegawai'),
                        TextEntry::make('tanggal_kirim')
                            ->label('Tanggal Penggajian')
                            ->date(),
                        TextEntry::make('jenis_gaji')
                            ->label('Jenis Gaji')
                            ->formatStateUsing(function (string $state): string {
                                return match ($state) {
                                    'gaji_pokok' => 'Gaji Pokok',
                                    'thr' => 'THR',
                                    'tunjangan' => 'Tunjangan',
                                    default => ucfirst($state),
                                };
                            }),
                    ])
                    ->columns(3),
                Section::make('Detail Keuangan')
                    ->schema([
                        // Detail untuk Gaji Pokok
                        ...($record->jenis_gaji === 'gaji_pokok' ? [
                            TextEntry::make('gross_salary')
                                ->label('Gaji Kotor')
                                ->money('IDR'),
                            TextEntry::make('total_bonuses')
                                ->label('Jumlah Bonus')
                                ->state(fn () => $total_bonuses)
                                ->money('IDR'),
                            TextEntry::make('tunjangan_mkn')
                                ->label('Tunjangan Makan')
                                ->state(fn () => $tunjangan_mkn)
                                ->money('IDR'),
                            TextEntry::make('tunjangan_trns')
                                ->label('Tunjangan Transportasi')
                                ->state(fn () => $tunjangan_trns)
                                ->money('IDR'),
                            TextEntry::make('total_deductions')
                                ->label('Potongan Denda')
                                ->state(fn () => $total_deductions)
                                ->money('IDR'),
                            TextEntry::make('asuransi')
                                ->label('Potongan Asuransi')
                                ->state(fn () => $asuransi)
                                ->money('IDR'),
                            TextEntry::make('pajak')
                                ->label('Potongan Pajak')
                                ->state(fn () => $pajak)
                                ->money('IDR'),
                            TextEntry::make('net_salary')
                                ->label('Total Gaji Bersih')
                                ->state(fn () => $gross_salary + $total_bonuses + $tunjangan_mkn + $tunjangan_trns - $total_deductions - $pajak - $asuransi)
                                ->money('IDR'),
                        ] : []),
                        // Detail untuk THR
                        ...($record->jenis_gaji === 'thr' ? [
                            TextEntry::make('masa_kerja')
                                ->label('Masa Kerja (Bulan)')
                                ->state(fn () => $masa_kerja_bulan),
                            TextEntry::make('gross_salary')
                                ->label('Gaji Kotor')
                                ->money('IDR'),
                            TextEntry::make('thr_calculation')
                                ->label('Tunjangan Posisi')
                                ->state(fn()=>$tunjangan)
                                ->money('IDR'),
                            TextEntry::make('net_salary')
                                ->label('Gaji Bersih')
                                ->state(fn ($record) => $record->net_salary)
                                ->money('IDR'),
                        ] : []),
                        // Detail untuk Tunjangan
                        ...($record->jenis_gaji === 'tunjangan' ? [
                            TextEntry::make('tunjangan')
                                ->label('Gaji Tunjangan')
                                ->state(fn () => $tunjangan)
                                ->money('IDR'),
                            TextEntry::make('net_salary')
                                ->label('Gaji Bersih')
                                ->state(fn ($record) => $record->net_salary)
                                ->money('IDR'),
                        ] : []),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getActions(): array
    {
        return [];
    }
}