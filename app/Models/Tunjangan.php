<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tunjangan extends Model
{
    protected $table = 'tunjangan';
    
    // Menentukan primary key sebagai UUID
    protected $primaryKey = 'tunjangan_id';
    public $incrementing = false; // Non-incrementing karena UUID
    protected $keyType = 'string'; // Tipe string untuk UUID

    protected $fillable = [
        'tunjangan_id', 'jenis_tunjangan', 'jumlah'
    ];

    protected $casts = [
        'iuran' => 'decimal:2', // Jumlah bonus dengan 2 desimal
    ];

    // Membuat UUID otomatis saat record baru dibuat
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->tunjangan_id)) {
                $model->tunjangan_id = (string) Str::uuid();
            }
        });
    }
}
