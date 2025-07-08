<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payroll;
use Illuminate\Support\Facades\Mail;
use App\Mail\PayrollApprovedMail;
use Carbon\Carbon;

class SendApprovedPayrolls extends Command
{
    protected $signature = 'payrolls:send';
    protected $description = 'Kirim payroll sekali atau tiap bulan sesuai tanggal_kirim';

    public function handle(): void
    {
        $today = now();

        // Payroll sekali kirim (hanya pada tanggal_kirim spesifik, belum dikirim)
        $oneTime = Payroll::where('approve_status', 'approved')
            ->whereDate('tanggal_kirim', $today->toDateString())
            ->whereNull('sent_at')
            ->get();

        foreach ($oneTime as $payroll) {
            Mail::to($payroll->email_penerima)->send(new PayrollApprovedMail($payroll));
            $payroll->update(['sent_at' => Carbon::now()]);
        }

        // Payroll bulanan (dikirim ulang setiap bulan pada hari yang sama)
        $monthly = Payroll::where('approve_status', 'approved')
            ->whereNotNull('sent_at')
            ->whereDay('tanggal_kirim', $today->day)
            ->get();

        foreach ($monthly as $payroll) {
            Mail::to($payroll->email_penerima)->send(new PayrollApprovedMail($payroll));
        }

        // Hitung total payroll yang dikirim
        $total = $oneTime->count() + $monthly->count();
        $this->info("Payrolls sent: " . $total);
    }
}
