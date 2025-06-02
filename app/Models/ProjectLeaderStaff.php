<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectLeaderStaff extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'project_leader_staff';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_user',
        'modul_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function modul()
    {
        return $this->belongsTo(ProjectModul::class, 'modul_id');
    }
}

