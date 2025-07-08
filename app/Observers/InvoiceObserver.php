<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\Finance;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class InvoiceObserver
{
    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        if ($invoice->approve_status === 'approved') {
            $this->recalculateUnpaidAmounts($invoice);
            $this->createFinanceEntry($invoice);
        }
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        if ($invoice->isDirty('approve_status') && $invoice->approve_status === 'approved') {
            $this->recalculateUnpaidAmounts($invoice);
            $this->createFinanceEntry($invoice);
        }
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleting(Invoice $invoice): void
    {
        Log::info('Processing deletion for invoice: ' . $invoice->invoice_id);
        $deleted = Finance::where('fk_invoice_id', $invoice->invoice_id)->delete();
        Log::info('Deleted Finance entries for fk_invoice_id: ' . $invoice->invoice_id . ', Rows affected: ' . $deleted);
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        $this->recalculateUnpaidAmounts($invoice);
    }

    /**
     * Recalculate unpaid_amount for modul and its parent project.
     */
    protected function recalculateUnpaidAmounts(Invoice $invoice): void
    {
        $modul = $invoice->modul;

        if (!$modul) {
            return;
        }

        // Total semua invoice yang sudah approved untuk modul ini
        $approvedInvoicesTotal = $modul->invoices()
            ->where('approve_status', 'approved')
            ->sum('invoice_amount');

        $modul->update([
            'unpaid_amount' => max($modul->alokasi_dana - $approvedInvoicesTotal, 0),
        ]);

        // Update unpaid_amount untuk parent project (GoingProject / Project)
        $project = $modul->project;

        if ($project) {
            $totalUnpaid = $project->modules()->sum('unpaid_amount');

            $project->update([
                'unpaid_amount' => $totalUnpaid,
            ]);
        }
    }

    /**
     * Create a new finance entry for the invoice.
     */
    protected function createFinanceEntry(Invoice $invoice): void
    {
        $existingFinance = Finance::where('fk_invoice_id', $invoice->invoice_id)->first();
        if (!$existingFinance) {
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
                'status_pembayaran' => 0,
            ]);
        }
    }
}