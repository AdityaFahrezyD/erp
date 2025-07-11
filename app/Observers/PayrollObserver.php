<?php

namespace App\Observers;

use App\Models\Payroll;
use App\Models\Finance;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PayrollObserver
{
    /**
     * Handle the Payroll "created" event.
     */
    public function created(Payroll $payroll): void
    {
        //
    }

    /**
     * Handle the Payroll "updated" event.
     */
    public function updated(Payroll $payroll)
    {
        if ($payroll->isDirty('approve_status') && $payroll->approve_status === 'approved') {
            // Cek apakah entri finance untuk payroll ini sudah ada
            $existingFinance = Finance::where('fk_payroll_id', $payroll->payroll_id)->first();
            if (!$existingFinance) {
                // Buat entri finance baru
                Finance::create([
                    'finance_id' => (string) Str::uuid(),
                    'transaction_id' => 'TRX-' . strtoupper(Str::random(8)),
                    'user_id' => $payroll->user_id,
                    'type' => 'payroll',
                    'fk_payroll_id' => $payroll->payroll_id,
                    'judul_transaksi' => $payroll->payroll_id,
                    'date' => $payroll->tanggal_kirim,
                    'notes' => 'Payroll: ' . $payroll->adjustment_desc,
                    'amount' => -$payroll->net_salary,
                    'status_pembayaran' => 1, // Sudah dibayar
                ]);
            }
        }
    }

    /**
     * Handle the Payroll "deleted" event.
     */
    public function deleting(Payroll $payroll): void
    {
        Log::info('Processing deletion for payroll: ' . $payroll->payroll_id);
        $deleted = Finance::where('fk_payroll_id', $payroll->payroll_id)->delete();
        Log::info('Deleted Finance entries for fk_payroll_id: ' . $payroll->payroll_id . ', Rows affected: ' . $deleted);
    }
    
     public function deleted(Payroll $payroll): void
    {
        //
    }

    /**
     * Handle the Payroll "restored" event.
     */
    public function restored(Payroll $payroll): void
    {
        //
    }

    /**
     * Handle the Payroll "force deleted" event.
     */
    public function forceDeleted(Payroll $payroll): void
    {
        //
    }
}
