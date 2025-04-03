<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Finance extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'finance';

    protected $primaryKey = 'finance_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'finance_id',
        'date',
        'description',
        'type',
        'amount',
        'saldo',
        'notes',
        'status_pembayaran',
        'approve_status',
    ];

    protected $casts = [
        'finance_id' => 'uuid',
        'date' => 'date',
    ];
}
