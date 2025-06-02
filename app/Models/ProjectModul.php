<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ProjectModul extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'project_modul';
    protected $fillable = [
        'project_id',
        'nama_modul',
        'deskripsi_modul',
        'alokasi_dana',
        'unpaid_amount',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function booted()
    {
        static::creating(function ($modul) {
            $modul->unpaid_amount = $modul->alokasi_dana ?? 0;
        });
    }

    public function updateUnpaidAmount()
    {
        $totalPaid = $this->invoices()->where('approve_status', 'approved')->sum('invoice_amount');
        $unpaid = max($this->alokasi_dana - $totalPaid, 0);

        $this->update([
            'unpaid_amount' => $unpaid,
        ]);
    }

    public function recalculateUnpaid()
    {
        $approvedInvoicesTotal = $this->invoices()
            ->where('approve_status', 'approved')
            ->sum('invoice_amount');

        $this->update([
            'unpaid_amount' => max($this->alokasi_dana - $approvedInvoicesTotal, 0),
        ]);

        if ($this->project) {
            $totalUnpaid = $this->project->modules()->sum('unpaid_amount');
            $this->project->update([
                'unpaid_amount' => $totalUnpaid,
            ]);
        }
    }


    public function project()
    {
        return $this->belongsTo(GoingProject::class, 'project_id', 'project_id');
    }

    public function sub_modul()
    {
        return $this->hasMany(SubModul::class, 'modul_id');
    }
    
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'modul_id', 'id');
    }
}
