<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestOtp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:otp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = \Illuminate\Support\Facades\Http::post('http://127.0.0.1:8000/auth/otp/send', [
            'email' => 'test@example.com'
        ]);
        $this->info($response->body());
    }
}
