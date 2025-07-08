<?php

namespace App\Observers;

use App\Models\OtherExpense;
use App\Models\Finance;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class OtherExpenseObserver
{
    /**
     * Handle the OtherExpense "created" event.
     */
    public function created(OtherExpense $otherExpense): void
    {
        //
    }

    /**
     * Handle the OtherExpense "updated" event.
     */
    public function updated(OtherExpense $otherExpense)
    {
            // Hanya lakukan jika approve_status berubah menjadi approved
            if ($otherExpense->isDirty('approve_status') && $otherExpense->approve_status === 'approved') {
            // Cek apakah entri finance untuk payroll ini sudah ada
            $existingFinance = Finance::where('fk_expense_id', $otherExpense->expense_id)->first();
            if (!$existingFinance) {
                // Buat entri finance baru
                    Finance::create([
                        'finance_id' => (string) Str::uuid(),
                        'transaction_id' => 'TRX-' . strtoupper(Str::random(8)),
                        'user_id' => $otherExpense->user_id,
                        'type' => 'other',
                        'fk_expense_id' => $otherExpense->expense_id,
                        'judul_transaksi' => $otherExpense->expense_id,
                        'date' => $otherExpense->tanggal,
                        'notes' => 'Other Expense: ' . $otherExpense->nama_pengeluaran,
                        'amount' => -$otherExpense->jumlah,
                        'status_pembayaran' => 1,  
                    ]);
                }
            }
    }

    /**
     * Handle the OtherExpense "deleted" event.
     */
    public function deleting(OtherExpense $otherExpense): void
    {
        Log::info('Processing deletion for expense: ' . $otherExpense->expense_id);
        $deleted = Finance::where('fk_expense_id', $otherExpense->expense_id)->delete();
        Log::info('Deleted Finance entries for fk_expense_id: ' . $otherExpense->expense_id . ', Rows affected: ' . $deleted);
    }
    
     public function deleted(OtherExpense $otherExpense): void
    {
        //
    }

    /**
     * Handle the OtherExpense "restored" event.
     */
    public function restored(OtherExpense $otherExpense): void
    {
        //
    }

    /**
     * Handle the OtherExpense "force deleted" event.
     */
    public function forceDeleted(OtherExpense $otherExpense): void
    {
        //
    }
}
