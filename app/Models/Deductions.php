<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Deductions extends Model
{
    protected $table = 'deductions';
    
    // Menentukan primary key sebagai UUID
    protected $primaryKey = 'deduction_id';
    public $incrementing = false; // Non-incrementing karena UUID
    protected $keyType = 'string'; // Tipe string untuk UUID

    // Kolom yang dapat diisi
    protected $fillable = [
        'deduction_id', 'fk_pegawai_id', 'keterangan', 'amount'
    ];

    // Casting untuk tipe data khusus
    protected $casts = [
        'amount' => 'decimal:2', // Jumlah pemotongan dengan 2 desimal
    ];

    // Relasi: Pemotongan milik satu payroll
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'fk_pegawai_id', 'pegawai_id');
    }

    // Membuat UUID otomatis saat record baru dibuat
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->deduction_id)) {
                $model->deduction_id = (string) Str::uuid();
            }
        });
    }
}
