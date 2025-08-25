<?php

namespace App\Jobs;

use App\Events\UserProgressEvent;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ScrapingJobStep3 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public $userId, public $jenisPajak)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $sessionData = Cache::get('selenium_session_' . $this->userId);
        if ($sessionData) {
            $driver = RemoteWebDriver::createBySessionID(
                $sessionData['sessionId'],
                $sessionData['serverUrl'],
                null,
                null,
                true,
                $sessionData['capabilities']
            );
            Log::info("Step3. Browser check: " . $driver->getTitle());

        }
    }
}
