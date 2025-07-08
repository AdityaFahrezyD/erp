<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pegawai extends Model
{
    protected $table = 'pegawai';
    protected $primaryKey = 'pegawai_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nama',
        'fk_posisi_id',
        'start_date',
        'phone',
        'email',
        'status',
        'tanggungan',
        'fk_asuransi_id',
        'base_salary',
        'pay_cycle',
    ];

    protected $casts = [
        'start_date' => 'date',
        'base_salary' => 'decimal:2',
    ];

    // Relasi ke tabel payroll
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class, 'pegawai_id', 'pegawai_id');
    }
    public function bonuses()
    {
        return $this->hasMany(Bonuses::class, 'fk_pegawai_id', 'pegawai_id');
    }

    public function deductions()
    {
        return $this->hasMany(Deductions::class, 'fk_pegawai_id', 'pegawai_id');
    }

     public function posisi()
    {
        return $this->belongsTo(Posisi::class, 'fk_posisi_id');
    }

    public function asuransi()
    {
        return $this->belongsTo(Asuransi::class, 'fk_asuransi_id');
    }

        public function pegawai()
    {
        return $this->hasMany(ProjectStaff::class, 'id_pegawai');
    }

    // Membuat UUID otomatis saat record baru dibuat
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->pegawai_id)) {
                $model->pegawai_id = (string) Str::uuid();
            }
        });
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }
}
