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
        'penerima',
        'keterangan',
        'harga',
        'email_penerima',
        'tanggal_kirim',
        'sent_at',
        'approve_status',
        'is_repeat',
    ];

    protected $casts = [
        'tanggal_kirim' => 'date',
        'approve_status' => 'string',
        'sent_at' => 'datetime',
        'is_repeat' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

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
}
