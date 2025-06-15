<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Bonuses extends Model
{
    protected $table = 'bonuses';
    
    // Menentukan primary key sebagai UUID
    protected $primaryKey = 'bonuses_id';
    public $incrementing = false; // Non-incrementing karena UUID
    protected $keyType = 'string'; // Tipe string untuk UUID

    // Kolom yang dapat diisi
    protected $fillable = [
        'bonuses_id', 'fk_pegawai_id', 'bonus_type', 'amount'
    ];

    // Casting untuk tipe data khusus
    protected $casts = [
        'amount' => 'decimal:2', // Jumlah bonus dengan 2 desimal
    ];

    // Relasi: Bonus milik satu payroll
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'fk_pegawai_id', 'pegawai_id');
    }

    // Membuat UUID otomatis saat record baru dibuat
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->bonuses_id)) {
                $model->bonuses_id = (string) Str::uuid();
            }
        });
    }
}
