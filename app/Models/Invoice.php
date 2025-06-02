<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'invoice';
    protected $primaryKey = 'invoice_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'project_id',
        'modul_id',
        'recipient',
        'company',
        'information',
        'invoice_amount',
        'recipient_email',
        'is_repeat',
        'send_date',
        'send_at',
        'approve_status',
    ];

    public function project()
    {
        return $this->belongsTo(GoingProject::class, 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function modul()
    {
        return $this->belongsTo(ProjectModul::class, 'modul_id');
    }

    protected static function booted()
    {
        static::created(function ($invoice) {
            \Log::info('Invoice created', ['status' => $invoice->approve_status]);

            if ($invoice->approve_status === 'approved' && $invoice->modul) {
                \Log::info('Reducing unpaid_amount on created', [
                    'modul_id' => $invoice->modul_id,
                    'amount' => $invoice->invoice_amount
                ]);
                $invoice->modul->decrement('unpaid_amount', $invoice->invoice_amount);
            }
        });

        static::updated(function ($invoice) {
            \Log::info('Invoice updated', ['status' => $invoice->approve_status]);

            if ($invoice->wasChanged('approve_status') && $invoice->approve_status === 'approved' && $invoice->modul) {
                \Log::info('Reducing unpaid_amount on updated', [
                    'modul_id' => $invoice->modul_id,
                    'amount' => $invoice->invoice_amount
                ]);
                $invoice->modul->decrement('unpaid_amount', $invoice->invoice_amount);
            }
        });
    }




}
