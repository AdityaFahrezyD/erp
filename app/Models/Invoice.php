<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Invoice extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'invoice';

    protected $primaryKey = 'invoice_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'invoice_id',
        'created_by',
        'penerima',
        'perusahaan',
        'keterangan',
        'harga',
        'email_penerima',
        'tipe',
        'tanggal_kirim',
        'approve_status',
    ];

    protected $casts = [
        'invoice_id' => 'uuid',
    ];
}
