<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DetectChangeEnv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:detect-change-env';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect changes in the .env file and clear configuration cache if needed';

    /**
     * The path to the .env file.
     */
    protected $envFilePath;

    /**
     * Path to store the checksum of .env file.
     */
    protected $checksumPath;

    public function __construct()
    {
        parent::__construct();
        $this->envFilePath = base_path('.env');
        $this->checksumPath = storage_path('app/.env.checksum');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!File::exists($this->envFilePath)) {
            $this->error('The .env file does not exist.');
            return;
        }

        // Calculate current checksum
        $currentChecksum = md5_file($this->envFilePath);

        // Check if checksum file exists
        if (File::exists($this->checksumPath)) {
            $lastChecksum = File::get($this->checksumPath);

            // Compare checksums
            if ($currentChecksum === $lastChecksum) {
                $this->info('No changes detected in .env file.');
                return;
            }
        }

        // Save the new checksum
        File::put($this->checksumPath, $currentChecksum);

        // Clear configuration cache
        $this->info('Changes detected in .env file. Clearing configuration cache...');
        $this->call('config:clear');
        $this->info('Successfully cleared configuration cache.');
    }
}
