<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectStaff extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'project_staff';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_user',
        'sub_modul_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function staff()
    {
        return $this->belongsTo(SubModul::class, 'sub_modul_id');
    }
}
