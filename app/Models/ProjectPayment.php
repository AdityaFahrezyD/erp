<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectPayment extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'project_payment';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_modul',
        'id_invoice',
        'jumlah_bayar',
        'deskkripsi',
    ];

    public function modul()
    {
        return $this->belongsTo(ProjectModul::class, 'id_modul');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'id_invoice', 'invoice_id');
    }
}

