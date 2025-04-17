<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Payroll extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'payroll';

    protected $primaryKey = 'payroll_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'payroll_id',
        'created_by',
        'penerima',
        'keterangan',
        'harga',
        'email_penerima',
        'tipe',
        'tanggal_kirim',
        'approve_status',
    ];

    protected $casts = [
        'payroll_id' => 'uuid',
    ];
}
