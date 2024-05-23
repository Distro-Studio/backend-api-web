<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendNotifyTransfer extends Mailable
{
    use Queueable, SerializesModels;

    public $nama;
    public $email;
    public $unitKerja_from;
    public $unitKerja_to;
    public $jabatan_from;
    public $jabatan_to;
    public $alasan;
    public $tanggal;

    public function __construct($nama, $email, $unitKerja_from, $unitKerja_to, $jabatan_from, $jabatan_to, $alasan, $tanggal)
    {
        $this->nama = $nama;
        $this->email = $email;
        $this->unitKerja_from = $unitKerja_from;
        $this->unitKerja_to = $unitKerja_to;
        $this->jabatan_from = $jabatan_from;
        $this->jabatan_to = $jabatan_to;
        $this->alasan = $alasan;
        $this->tanggal = $tanggal;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Transfer Karyawan {$this->nama} Berhasil!",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.sendingNotifyTransfer',
            with: [
                'nama' => $this->nama,
                'email' => $this->email,
                'unitKerja_from' => $this->unitKerja_from,
                'unitKerja_to' => $this->unitKerja_to,
                'jabatan_from' => $this->jabatan_from,
                'jabatan_to' => $this->jabatan_to,
                'alasan' => $this->alasan,
                'tanggal' => $this->tanggal
            ]
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
