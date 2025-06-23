<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Asuransi extends Model
{
    protected $table = 'asuransi';
    
    // Menentukan primary key sebagai UUID
    protected $primaryKey = 'asuransi_id';
    public $incrementing = false; // Non-incrementing karena UUID
    protected $keyType = 'string'; // Tipe string untuk UUID

    protected $fillable = [
        'asuransi_id', 'tingkat', 'iuran'
    ];

    protected $casts = [
        'iuran' => 'decimal:2', // Jumlah bonus dengan 2 desimal
    ];

    // Membuat UUID otomatis saat record baru dibuat
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->asuransi_id)) {
                $model->asuransi_id = (string) Str::uuid();
            }
        });
    }
}
