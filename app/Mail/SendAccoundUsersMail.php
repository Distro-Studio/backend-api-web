<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendAccoundUsersMail extends Mailable
{
    use Queueable, SerializesModels;

    public $username;
    public $password;
    public $nama;

    public function __construct($username, $password, $nama)
    {
        $this->username = $username;
        $this->password = $password;
        $this->nama = $nama;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->nama}, Akun Anda Berhasil Dibuat - Selamat Datang di Platform Karyawan RSKI Kota Surakarta",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.sendingAccount',
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
