<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Invoice $invoice;
    public $pdf;

    public function __construct($invoice, $pdf)
    {
        $this->invoice = $invoice;
        $this->pdf = $pdf;
    }

    public function build()
    {
        return $this->subject('Invoice #' . $this->invoice->invoice_id)
            ->view('emails.invoice-approved')
            ->attachData($this->pdf, 'invoice-' . $this->invoice->invoice_id . '.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}