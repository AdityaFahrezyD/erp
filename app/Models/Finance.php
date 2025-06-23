<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Optional

class Finance extends Model
{
    use HasFactory;
    // use SoftDeletes; // Uncomment if you want to use soft deletes

    protected $fillable = [
        'finance_id',
        'transaction_id',
        'user_id',
        'type',
        'date',
        'amount',
        'saldo',
        'status_pembayaran',
        'notes',
        'fk_invoice_id',
        'fk_payroll_id',
        'fk_expense_id',
        'judul_transaksi',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'saldo' => 'decimal:2',
        'status_pembayaran' => 'integer',
    ];

    /**
     * Update the saldo (balance) when saving the record
     * This method automatically calculates the balance when a record is created or updated
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($finance) {
            $latestTransaction = self::where('date', '<', $finance->date)
                ->where('status_pembayaran', 1)
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            $currentBalance = $latestTransaction ? $latestTransaction->saldo : 0;
            
            if ($finance->status_pembayaran == 1) {
                $finance->saldo = $currentBalance + $finance->amount;
            } else {
                $finance->saldo = $currentBalance;
            }
        });

        static::updating(function ($finance) {
            if ($finance->isDirty('status_pembayaran') && $finance->status_pembayaran == 1) {
                $latestTransaction = self::where('date', '<', $finance->date)
                    ->where('status_pembayaran', 1)
                    ->orderBy('date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();
                $currentBalance = $latestTransaction ? $latestTransaction->saldo : 0;
                $finance->saldo = $currentBalance + $finance->amount;
            }
            
            if ($finance->isDirty('amount') && $finance->status_pembayaran == 1) {
                $latestTransaction = self::where('date', '<', $finance->date)
                    ->where('status_pembayaran', 1)
                    ->orderBy('date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();
                $currentBalance = $latestTransaction ? $latestTransaction->saldo : 0;
                $finance->saldo = $currentBalance + $finance->amount;
            }
            
            if ($finance->isDirty('status_pembayaran') && $finance->getOriginal('status_pembayaran') == 1 && $finance->status_pembayaran != 1) {
                $finance->saldo = $finance->getOriginal('saldo') - $finance->amount;
            }
        });
        
        // After a record is updated, we may need to recalculate subsequent records
        static::saved(function ($finance) {
            if ($finance->wasChanged('status_pembayaran') || $finance->wasChanged('amount')) {
                $allRecords = self::where('date', '>=', $finance->date)
                    ->orderBy('date', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();
                
                $currentBalance = 0;
                foreach ($allRecords as $index => $record) {
                    if ($record->status_pembayaran == 1) {
                        $currentBalance += $record->amount;
                    }
                    if ($record->id !== $finance->id) { // Hindari update diri sendiri
                        self::where('id', $record->id)->update(['saldo' => $currentBalance]);
                    }
                }
            }
        });
    }

    /**
     * Get the user that owns this finance record
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'fk_invoice_id');
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class, 'fk_payroll_id');
    }

    public function other_expense()
    {
        return $this->belongsTo(OtherExpense::class, 'fk_expense_id');
    }
}