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
        // Saat transaksi baru dibuat
        static::creating(function ($finance) {
            // Set saldo awal berdasarkan transaksi sebelumnya
            $latestTransaction = self::where('date', '<=', $finance->date)
                ->where('status_pembayaran', 1)
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $finance->saldo = $latestTransaction ? $latestTransaction->saldo : 0;
        });

        // Saat transaksi disimpan (create atau update)
        static::saved(function ($finance) {
            self::recalculateBalances();
        });

        // Saat transaksi dihapus
        static::deleted(function ($finance) {
            self::recalculateBalances();
        });
    }
    public static function recalculateBalances()
    {
        $transactions = self::orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $currentBalance = 0;

        foreach ($transactions as $transaction) {
            if ($transaction->status_pembayaran == 1) {
                $currentBalance += $transaction->amount;
            }
            $transaction->updateQuietly(['saldo' => $currentBalance]);
        }
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