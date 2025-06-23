<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Posisi extends Model
{
    protected $table = 'posisi';
    
    // Menentukan primary key sebagai UUID
    protected $primaryKey = 'posisi_id';
    public $incrementing = false; // Non-incrementing karena UUID
    protected $keyType = 'string'; // Tipe string untuk UUID

    protected $fillable = [
        'posisi_id', 'posisi', 'gaji', 'tunjangan'
    ];

    protected $casts = [
        'gaji' => 'decimal:2',
        'tunjangan' => 'decimal:2', // Jumlah bonus dengan 2 desimal
    ];

    // Membuat UUID otomatis saat record baru dibuat
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->posisi_id)) {
                $model->posisi_id = (string) Str::uuid();
            }
        });
    }
}
