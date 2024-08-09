<?php

namespace App\Jobs\EmailNotification;

use Illuminate\Bus\Queueable;
use App\Mail\SendAccoundUsersMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AccountEmailJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  protected $email;
  // protected $username;
  protected $password;
  protected $nama;

  /**
   * Create a new job instance.
   */
  public function __construct($email, $password, $nama)
  {
    $this->email = $email;
    // $this->username = $username;
    $this->password = $password;
    $this->nama = $nama;
  }

  /**
   * Execute the job.
   */
  public function handle(): void
  {
    Mail::to($this->email)->send(new SendAccoundUsersMail($this->email, $this->password, $this->nama));
  }
}
