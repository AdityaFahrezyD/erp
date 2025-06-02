<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubModul extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'sub_modul';
    protected $fillable = [
        'modul_id',
        'nama_sub_modul',
        'deskripsi_sub_modul',
    ];

    public $incrementing = false;
    protected $keyType = 'string';


    public function sub_modul()
    {
        return $this->belongsTo(ProjectModul::class, 'modul_id');
    }

    public function staff()
    {
        return $this->hasMany(ProjectStaff::class, 'sub_modul_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'modul_id', 'id');
    }
        public function modul()
    {
        return $this->belongsTo(ProjectModul::class, 'modul_id');
    }

}
