<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ResetPassword extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'reset_password';

    protected $primaryKey = 'id_reset';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_reset',
        'email',
        'token',
        'expired_at',
        'timestamp',
    ];

    protected $casts = [
        'id_reset' => 'uuid',
    ];
}
