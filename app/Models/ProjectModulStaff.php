<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProjectModulStaff extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'project_modul_staff';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'project_modul_id',
        'project_staff_id',
        'status_pengerjaan',
    ];

    protected $casts = [
        'status_pengerjaan' => 'string',
    ];

    public function projectModul()
    {
        return $this->belongsTo(ProjectModul::class, 'project_modul_id');
    }

    public function projectStaff()
    {
        return $this->belongsTo(ProjectStaff::class, 'project_staff_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'modul_id');
    }

    public function project()
    {
        return $this->belongsTo(GoingProject::class, 'project_id');
    }

}
