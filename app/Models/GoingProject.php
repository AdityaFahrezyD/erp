<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\ProjectPayment;


class GoingProject extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'project_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'project_name',
        'total_harga_proyek',
        'unpaid_amount',
        'status',
        'batas_awal',
        'batas_akhir',
        'harga_awal',
        'company',
        'pic',
        'pic_email',
        'project_leader',
    ];

    protected $casts = [
        'batas_awal' => 'date',
        'batas_akhir' => 'date',
    ];

    public function modules()
    {
        return $this->hasMany(ProjectModul::class, 'project_id', 'project_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'project_id', 'project_id');
    }
    public function payments()
    {
        return $this->hasMany(ProjectPayment::class);
    }
    public function staff()
    {
        return $this->hasMany(ProjectStaff::class);
    }

    public function leader()
    {
        return $this->belongsTo(Pegawai::class, 'project_leader');
    }
}
