<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class GoingProject extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'going_projects';

    protected $primaryKey = 'project_id';

    public $incrementing = false;
    
    protected $keyType = 'string';

    protected $fillable = [
        'project_id',
        'project_name',
        'unpaid_amount',
        'status',
    ];

    protected $casts = [
        'unpaid_amount' => 'float',
    ];
}

