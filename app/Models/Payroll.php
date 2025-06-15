<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

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
        'gross_salary',
        'net_salary',
        'email_penerima',
        'tanggal_kirim',
        'sent_at',
        'approve_status',
        'adjustment',
        'adjustment_desc',
    ];

    protected $casts = [
        'tanggal_kirim' => 'date',
        'approve_status' => 'string',
        'sent_at' => 'datetime',
        'adjustment' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($payroll) {
            // Ambil total deductions dan bonuses berdasarkan fk_pegawai_id
            $total_deductions = Deductions::where('fk_pegawai_id', $payroll->fk_pegawai_id)->sum('amount') ?? 0;
            $total_bonuses = Bonuses::where('fk_pegawai_id', $payroll->fk_pegawai_id)->sum('amount') ?? 0;

            // Hitung net_salary
            $payroll->net_salary = $payroll->gross_salary - $total_deductions + $total_bonuses;

            // Validasi agar net_salary tidak negatif (opsional)
            if ($payroll->net_salary < 0) {
                throw new \Exception('Net salary cannot be negative.');
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


}
