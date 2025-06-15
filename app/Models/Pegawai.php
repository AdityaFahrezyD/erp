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
        'pegawai_id',
        'nama',
        'position',
        'start_date',
        'phone',
        'email',
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
}
