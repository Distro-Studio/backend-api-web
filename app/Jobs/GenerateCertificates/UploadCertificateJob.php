<?php

namespace App\Jobs\GenerateCertificates;

use App\Helpers\GenerateCertificateHelper;
use App\Models\Diklat;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UploadCertificateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $diklat;
    protected $user;

    public function __construct(Diklat $diklat, User $user)
    {
        $this->diklat = $diklat;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Panggil helper generate sertifikat
        GenerateCertificateHelper::generateCertificate($this->diklat, $this->user);
    }
}
