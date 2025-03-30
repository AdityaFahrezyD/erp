<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
class Invoice extends Model
{
    use HasUuids;
    protected $table = 'invoice';
    protected $primaryKey = 'invoice_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'project_id',
        'penerima',
        'perusahaan',
        'keterangan',
        'harga',
        'email_penerima',
        'tipe',
        'tanggal_kirim',
        'approve_status',
    ];

    public function project()
    {
        return $this->belongsTo(GoingProject::class, 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
