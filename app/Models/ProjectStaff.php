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
        'id_pegawai',
        'sub_modul_id',
        'status',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function staff()
    {
        return $this->belongsTo(SubModul::class, 'sub_modul_id');
    }
}
