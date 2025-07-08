<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubModulDependencies extends Model
{
    use HasFactory;

    protected $table = 'sub_modul_dependencies';

    protected $fillable = [
        'sub_modul_id',
        'depends_on_sub_modul_id',
    ];

    public $incrementing = false;
    public $timestamps = true;
}

