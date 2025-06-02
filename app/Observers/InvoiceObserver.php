<?php

namespace App\Observers;

use App\Models\Invoice;

class InvoiceObserver
{
    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        if ($invoice->approve_status === 'approved') {
            $this->recalculateUnpaidAmounts($invoice);
        }
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice)
    {
        // Hanya lakukan jika approve_status berubah menjadi approved
        if ($invoice->isDirty('approve_status') && $invoice->approve_status === 'approved') {
            $this->recalculateUnpaidAmounts($invoice);
        }
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

        // Total semua invoice yg sudah approved untuk modul ini
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
}
