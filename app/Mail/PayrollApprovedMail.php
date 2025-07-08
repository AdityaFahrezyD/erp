<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Bonuses;
use App\Models\Deductions;
use Carbon\Carbon;

class PayrollApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payroll;

    public function __construct(Payroll $payroll)
    {
        $this->payroll = $payroll;
    }

    public function build()
    {
        // Hitung data untuk PDF (sama seperti di ViewPayroll)
        $gross_salary = $this->payroll->gross_salary;
        $total_bonuses = Bonuses::where('fk_pegawai_id', $this->payroll->fk_pegawai_id)->sum('amount') ?? 0;
        $total_deductions = Deductions::where('fk_pegawai_id', $this->payroll->fk_pegawai_id)->sum('amount') ?? 0;
        $tunjangan_mkn = 700000;
        $tunjangan_trns = 1000000;
        $asuransi = $this->payroll->pegawai->asuransi->iuran ?? 0;
        $tunjangan = $this->payroll->pegawai->posisi->tunjangan ?? 0;

        $pajak = 0;
        if ($this->payroll->jenis_gaji === 'gaji_pokok') {
            $penghasilan_bulanan = $gross_salary + $total_bonuses + $tunjangan_mkn + $tunjangan_trns - $total_deductions - $asuransi;
            $penghasilan_tahunan = $penghasilan_bulanan * 12;

            $statusPajak = match (true) {
                $this->payroll->pegawai->status === 'single' => 'TK0',
                $this->payroll->pegawai->status === 'married' && $this->payroll->pegawai->tanggungan === 0 => 'K0',
                $this->payroll->pegawai->status === 'married' && $this->payroll->pegawai->tanggungan === 1 => 'K1',
                $this->payroll->pegawai->status === 'married' && $this->payroll->pegawai->tanggungan === 2 => 'K2',
                $this->payroll->pegawai->status === 'married' && $this->payroll->pegawai->tanggungan >= 3 => 'K3',
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

        $masa_kerja_bulan = 0;
        if ($this->payroll->jenis_gaji === 'thr') {
            $start_date = Carbon::parse($this->payroll->pegawai->start_date);
            $masa_kerja_bulan = max(0, (int) $start_date->diffInMonths(now()));
        }

        // Generate PDF
        $pdf = Pdf::loadView('emails.payroll.detail-pdf', [
            'payroll' => $this->payroll,
            'gross_salary' => $gross_salary,
            'total_bonuses' => $total_bonuses,
            'total_deductions' => $total_deductions,
            'tunjangan_mkn' => $tunjangan_mkn,
            'tunjangan_trns' => $tunjangan_trns,
            'asuransi' => $asuransi,
            'pajak' => $pajak,
            'masa_kerja_bulan' => $masa_kerja_bulan,
            'tunjangan' => $tunjangan
        ]);

        return $this->view('emails.payroll.approved')
                    ->subject('Payroll Anda Telah Disetujui')
                    ->attachData($pdf->output(), 'Detail_Payroll_' . $this->payroll->payroll_id . '.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}
