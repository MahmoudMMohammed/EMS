<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Factory;

class GenerateFcmToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fcm:generate-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a Firebase custom token';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $serviceAccountPath = storage_path('firebase/fcm.json'); // Update path

        $factory = (new Factory)->withServiceAccount($serviceAccountPath);
        $auth = $factory->createAuth();

        $uid = "1"; // Replace with actual unique user ID
        $customToken = $auth->createCustomToken($uid);

        $this->info($customToken->toString());
    }
}
