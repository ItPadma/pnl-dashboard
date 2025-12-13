<?php

namespace App\Jobs;

use App\Events\UserDataEvent;
use App\Events\UserEvent;
use App\Events\UserProgressEvent;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ScrapingJob implements ShouldQueue
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
        $host = 'http://localhost:4444/wd/hub';
        $options = new ChromeOptions();
        $options->addArguments([
            '--start-maximized',
            '--no-sandbox',
            '--disable-gpu',
            '--disable-dev-shm-usage',
            '--disable-extensions',
            '--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36',
            '--disable-blink-features=AutomationControlled',
            '--disable-features=Sensor',
            '--deny-permission-prompts'
        ]);
        $options->setExperimentalOption("excludeSwitches", ["enable-automation"]);
        $options->setExperimentalOption("useAutomationExtension", false);
        $options->setExperimentalOption('prefs', [
            'credentials_enable_service' => false,
            'profile.password_manager_enabled' => false
        ]);
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

        $driver = RemoteWebDriver::create($host, $capabilities);

        $driver->executeScript("Object.defineProperty(navigator, 'webdriver', {get: () => undefined})");

        try {
            broadcast(new UserProgressEvent('info', 'CoreTax Scraping', 5, 'Membuka portal login CoreTax...', $this->userId));
            $driver->get('https://coretaxdjp.pajak.go.id/identityproviderportal/Account/Login');
            $captchaElement = $driver->findElement(WebDriverBy::id('dntCaptchaImg'));

            // fill id and password
            $driver->findElement(WebDriverBy::id('Username'))->sendKeys(env('CORETAX_ID'));
            $driver->findElement(WebDriverBy::id('password'))->sendKeys(env('CORETAX_PASS'));
            broadcast(new UserProgressEvent('info', 'CoreTax Scraping', 10, 'Mengisi form login...', $this->userId));

            sleep(3); // wait for the captcha image to load
            $now = now()->format('YmdHis');
            $captchaImage = $captchaElement->takeElementScreenshot(public_path("assets/captcha-{$now}.png"));
            broadcast(new UserProgressEvent('info', 'CoreTax Scraping', 15, 'Mengambil screenshot captcha...', $this->userId));

            broadcast(new UserDataEvent('success', 'CoreTax Captcha', "assets/captcha-{$now}.png", $this->userId));
            broadcast(new UserProgressEvent('info', 'CoreTax Scraping', 20, 'Menunggu verifikasi captcha...', $this->userId));

            $sessionId = $driver->getSessionID();
            $serverUrl = $host;
            // clear cache before put
            Cache::forget('selenium_session_' . $this->userId);
            Cache::put('selenium_session_' . $this->userId, [
                'sessionId' => $sessionId,
                'serverUrl' => $serverUrl,
                'capabilities' => $capabilities
            ], now()->addMinutes(120));
            ScrapingJobStep2::dispatch($this->userId, $this->jenisPajak);
        } catch (\Throwable $th) {
            Log::error('Coretax Scraping Error: ' . $th->getMessage());
            broadcast(new UserProgressEvent('error', 'CoreTax Scraping', 100, 'Error: ' . $th->getMessage(), $this->userId));
            broadcast(new UserEvent('error', 'CoreTax Scraping', 'Error: ' . $th->getMessage(), $this->userId));
            if (isset($driver)) {
                $driver->quit();
            }
        }
    }
}
