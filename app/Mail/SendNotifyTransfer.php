<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNotifyTransfer extends Mailable
{
    use Queueable, SerializesModels;

    public $nama;
    public $email;
    public $unit_kerja_asals;
    public $unit_kerja_tujuans;
    public $jabatan_asals;
    public $jabatan_tujuans;
    public $alasan;
    public $tanggal_mulai;

    public function __construct($nama, $email, $unit_kerja_asals, $unit_kerja_tujuans, $jabatan_asals, $jabatan_tujuans, $alasan, $tanggal_mulai)
    {
        $this->nama = $nama;
        $this->email = $email;
        $this->unit_kerja_asals = $unit_kerja_asals;
        $this->unit_kerja_tujuans = $unit_kerja_tujuans;
        $this->jabatan_asals = $jabatan_asals;
        $this->jabatan_tujuans = $jabatan_tujuans;
        $this->alasan = $alasan;

        Carbon::setLocale('id');
        $tanggal = Carbon::parse($tanggal_mulai)->translatedFormat('l, j F Y');
        $waktu = Carbon::parse($tanggal_mulai)->translatedFormat('H:i');
        $this->tanggal_mulai = $tanggal . ' pukul ' . $waktu . ' WIB';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Pemberitahuan Pemindahan Unit Kerja dan Jabatan Karyawan: {$this->nama}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.sendingNotifyTransfer',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
