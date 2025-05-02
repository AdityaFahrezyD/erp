<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceApprovedMail;
use Carbon\Carbon;

class SendApprovedInvoices extends Command
{
    protected $signature = 'invoices:send';
    protected $description = 'Kirim invoice approved, baik one-time maupun bulanan';

    public function handle(): void
    {
        $today = now();

        $invoices = Invoice::where('approve_status', 'approved')
            ->where(function ($query) use ($today) {
                $query
                    ->where(function ($q) use ($today) {
                        $q->where('is_repeat', false)
                        ->whereNull('sent_at')
                        ->whereDate('send_date', '<=', $today->toDateString());
                    })
                    ->orWhere(function ($q) use ($today) {
                        $q->where('is_repeat', true)
                        ->whereDay('send_date', '<=', $today->day)
                        ->where(function ($qq) use ($today) {
                            $qq->whereNull('sent_at')
                                ->orWhereMonth('sent_at', '!=', $today->month);
                        });
                    });
            })->get();


        foreach ($invoices as $invoice) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf', [
                'invoice' => $invoice,
            ])->output();
            Mail::to($invoice->recipient_email)->send(new InvoiceApprovedMail($invoice, $pdf));
            $invoice->sent_at = now();
            $invoice->save();
        }

        $this->info("Invoices sent: " . $invoices->count());
    }
}
