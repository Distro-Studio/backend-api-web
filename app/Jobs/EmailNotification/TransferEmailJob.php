<?php

namespace App\Jobs\EmailNotification;

use Illuminate\Bus\Queueable;
use App\Mail\SendNotifyTransfer;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TransferEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $recipientEmail;
    protected $ccEmails;
    protected $details;

    /**
     * Create a new job instance.
     */
    public function __construct($recipientEmail, $ccEmails, $details)
    {
        $this->recipientEmail = $recipientEmail;
        $this->ccEmails = $ccEmails;
        $this->details = $details;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $email = new SendNotifyTransfer(
            $this->details['nama'],
            $this->details['email'],
            $this->details['unit_kerja_asals'],
            $this->details['unit_kerja_tujuans'],
            $this->details['jabatan_asals'],
            $this->details['jabatan_tujuans'],
            $this->details['kelompok_gaji_asals'],
            $this->details['kelompok_gaji_tujuans'],
            $this->details['role_tujuans'],
            $this->details['alasan'],
            $this->details['tgl_mulai']
        );

        if (!empty($this->ccEmails)) {
            Mail::to($this->recipientEmail)->cc($this->ccEmails)->send($email);
        } else {
            Mail::to($this->recipientEmail)->send($email);
        }
    }
}
