<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class GoingProject extends Model
{
    use HasUuids;

    protected $fillable = [
        'project_name',
        'unpaid_amount',
    ];
}
