<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\Finance;
use Illuminate\Support\Str;

class InvoiceObserver
{
    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice)
    {
        if ($invoice->isDirty('approve_status') && $invoice->approve_status === 'approved') {
            // Cek apakah entri finance untuk invoice ini sudah ada
            $existingFinance = Finance::where('fk_invoice_id', $invoice->invoice_id)->first();
            if (!$existingFinance) {
                // Buat entri finance baru
                Finance::create([
                    'finance_id' => (string) Str::uuid(),
                    'transaction_id' => 'TRX-' . strtoupper(Str::random(8)),
                    'user_id' => $invoice->user_id,
                    'type' => 'invoice',
                    'fk_invoice_id' => $invoice->invoice_id,
                    'judul_transaksi' => $invoice->invoice_id,
                    'date' => $invoice->send_date,
                    'notes' => 'Invoice: ' . $invoice->information,
                    'amount' => $invoice->invoice_amount,
                    'status_pembayaran' => 1, // Sudah dibayar
                    'approve_status' => 1, // Sudah di-approve
                ]);
            }
        }
    }

    /**
     * Handle the Invoice "updated" event.
        */
    public function updated(Invoice $invoice)
    {
        if ($invoice->isDirty('approve_status') && $invoice->approve_status === 'approved') {
            // Cek apakah entri finance untuk invoice ini sudah ada
            $existingFinance = Finance::where('fk_invoice_id', $invoice->invoice_id)->first();
            if (!$existingFinance) {
                // Buat entri finance baru
                Finance::create([
                    'finance_id' => (string) Str::uuid(),
                    'transaction_id' => 'TRX-' . strtoupper(Str::random(8)),
                    'user_id' => $invoice->user_id,
                    'type' => 'invoice',
                    'fk_invoice_id' => $invoice->invoice_id,
                    'judul_transaksi' => $invoice->invoice_id,
                    'date' => $invoice->send_date,
                    'notes' => 'Invoice: ' . $invoice->information,
                    'amount' => $invoice->invoice_amount,
                    'status_pembayaran' => 1, // Sudah dibayar
                    'approve_status' => 1, // Sudah di-approve
                ]);
            }
        }
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        //
    }

    /**
     * Handle the Invoice "restored" event.
     */
    public function restored(Invoice $invoice): void
    {
        //
    }

    /**
     * Handle the Invoice "force deleted" event.
     */
    public function forceDeleted(Invoice $invoice): void
    {
        //
    }
}
