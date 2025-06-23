<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'payroll';
    protected $primaryKey = 'payroll_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'payroll_id',
        'user_id',
        'fk_pegawai_id',
        'jenis_gaji',
        'gross_salary',
        'net_salary',
        'email_penerima',
        'tanggal_kirim',
        'sent_at',
        'approve_status',
    ];

    protected $casts = [
        'tanggal_kirim' => 'date',
        'approve_status' => 'string',
        'sent_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($payroll) {
            // Hanya hitung ulang jika net_salary belum diatur
            if (!isset($payroll->attributes['net_salary']) || is_null($payroll->attributes['net_salary'])) {
                $payroll->calculateSalary();
            }
        });

        static::updated(function ($payroll) {
            // Tandai bonus dan denda sebagai digunakan hanya jika approve_status menjadi 'approved'
            if ($payroll->wasChanged('approve_status') && $payroll->approve_status === 'approved') {
                if ($payroll->jenis_gaji === 'gaji_pokok') {
                    Bonuses::where('fk_pegawai_id', $payroll->fk_pegawai_id)
                        ->where('is_used', false)
                        ->update(['is_used' => true]);
                }
                Deductions::where('fk_pegawai_id', $payroll->fk_pegawai_id)
                    ->where('is_used', false)
                    ->update(['is_used' => true]);

                // Log untuk debugging
                Log::info('Bonus dan Denda Ditandai sebagai Digunakan', [
                    'payroll_id' => $payroll->payroll_id,
                    'fk_pegawai_id' => $payroll->fk_pegawai_id,
                    'jenis_gaji' => $payroll->jenis_gaji,
                    'approve_status' => $payroll->approve_status,
                ]);
            }
        });

        static::creating(function ($model) {
            if (empty($model->payroll_id)) {
                $model->payroll_id = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'fk_pegawai_id', 'pegawai_id');
    }

    public function calculateSalary()
    {
        $pegawai = $this->pegawai()->with(['posisi', 'asuransi'])->first();
        if (!$pegawai) {
            throw new \Exception('Pegawai tidak ditemukan.');
        }

        // Hanya ambil bonus dan denda yang belum digunakan
        $total_bonuses = Bonuses::where('fk_pegawai_id', $this->fk_pegawai_id)
            ->where('is_used', false)
            ->sum('amount') ?? 0;
        $total_deductions = Deductions::where('fk_pegawai_id', $this->fk_pegawai_id)
            ->where('is_used', false)
            ->sum('amount') ?? 0;
        $tunjangan_mkn = 0;
        $tunjangan_trns = 0;
        $asuransi = $pegawai->asuransi->iuran ?? 0;
        $tunjangan = $pegawai->posisi->tunjangan ?? 0;
        $pajak = 0;

        // Log untuk debugging
        Log::info('Perhitungan Gaji', [
            'fk_pegawai_id' => $this->fk_pegawai_id,
            'jenis_gaji' => $this->jenis_gaji,
            'total_bonuses' => $total_bonuses,
            'total_deductions' => $total_deductions,
        ]);

        if ($this->jenis_gaji === 'gaji_pokok') {
            $tunjangan_mkn = 700000;
            $tunjangan_trns = 1000000;
            $this->gross_salary = $pegawai->base_salary;

            // Hitung pajak
            $penghasilan_bulanan = $this->gross_salary + $total_bonuses + $tunjangan_mkn + $tunjangan_trns - $total_deductions - $asuransi;
            $penghasilan_tahunan = $penghasilan_bulanan * 12;

            $statusPajak = match (true) {
                $pegawai->status === 'single' => 'TK0',
                $pegawai->status === 'married' && $pegawai->tanggungan === 0 => 'K0',
                $pegawai->status === 'married' && $pegawai->tanggungan === 1 => 'K1',
                $pegawai->status === 'married' && $pegawai->tanggungan === 2 => 'K2',
                $pegawai->status === 'married' && $pegawai->tanggungan >= 3 => 'K3',
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

            // Hitung net_salary untuk gaji pokok
            $this->net_salary = $this->gross_salary + $total_bonuses + $tunjangan_mkn + $tunjangan_trns - $total_deductions - $pajak - $asuransi;
        } elseif ($this->jenis_gaji === 'thr') {
            $start_date = Carbon::parse($pegawai->start_date);
            $masa_kerja_bulan = max(0, (int) $start_date->diffInMonths(now()));
            $this->gross_salary = $masa_kerja_bulan >= 12
                ? $pegawai->base_salary + $tunjangan
                : ($masa_kerja_bulan / 12) * $pegawai->base_salary;

            // Net salary untuk THR: hanya gross_salary dikurangi denda
            $this->net_salary = $this->gross_salary;
        } elseif ($this->jenis_gaji === 'tunjangan') {
            $this->gross_salary = $tunjangan;

            // Net salary untuk tunjangan: hanya gross_salary dikurangi denda
            $this->net_salary = $this->gross_salary;
        }

        // Validasi net_salary tidak negatif
        if ($this->net_salary < 0) {
            throw new \Exception('Net salary cannot be negative.');
        }

        // Log hasil perhitungan
        Log::info('Hasil Perhitungan', [
            'gross_salary' => $this->gross_salary,
            'net_salary' => $this->net_salary,
            'pajak' => $pajak,
            'asuransi' => $asuransi,
            'tunjangan_mkn' => $tunjangan_mkn,
            'tunjangan_trns' => $tunjangan_trns,
            'total_bonuses' => $total_bonuses,
            'total_deductions' => $total_deductions,
        ]);
    }
}