<?php

namespace App\Jobs;

use App\Events\UserProgressEvent;
use App\Models\Captcha;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ScrapingJobStep2 implements ShouldQueue
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
            Log::info("Step2. Browser check: " . $driver->getTitle());
            sleep(10);
            $inputCaptcha = Captcha::where('user_id', $this->userId)->latest()->first();
            broadcast(new UserProgressEvent('info', 'CoreTax Scraping', 30, 'Mengisi captcha...', $this->userId));
            $driver->findElement(WebDriverBy::id('DNTCaptchaInputText'))->sendKeys($inputCaptcha->captcha);
            broadcast(new UserProgressEvent('info', 'CoreTax Scraping', 30, 'Login...', $this->userId));
            $driver->findElement(WebDriverBy::name('button'))->click();
            // impersonating
            broadcast(new UserProgressEvent('info', 'CoreTax Scraping', 30, 'Impersonating...', $this->userId));
            // tunggu hingga link https://coretaxdjp.pajak.go.id/registration-portal/id-ID/reg-home di-load
            $driver->wait()->until(
                WebDriverExpectedCondition::urlContains('https://coretaxdjp.pajak.go.id/registration-portal/id-ID/reg-home')
            );
            // click element span yang teksnya "Pilih"
            $driver->findElement(WebDriverBy::id("pr_id_3_label"))->click();
            sleep(rand(1, 3));
            // send arrow down key
            $driver->getKeyboard()->pressKey(WebDriverKeys::ARROW_DOWN);
            // tekan arah bawah
            $driver->wait()->until(
                WebDriverExpectedCondition::urlContains('https://coretaxdjp.pajak.go.id/registration-portal/id-ID/reg-home')
            );
            // click menu e-Faktur
            broadcast(new UserProgressEvent('info', 'CoreTax Scraping', 32, 'Membuka menu e-Faktur...', $this->userId));
            $driver->findElement(WebDriverBy::linkText("e-Faktur"))->click();
            // click menu Pajak
            broadcast(new UserProgressEvent('info', 'CoreTax Scraping', 34, 'Membuka menu Pajak...', $this->userId));
            $driver->wait()->until(
                WebDriverExpectedCondition::urlContains('https://coretaxdjp.pajak.go.id/e-invoice-portal/id-ID/e-invoice-dashboard')
            );
            switch ($this->jenisPajak) {
                case '1':
                    // click menu id 86
                    broadcast(new UserProgressEvent('info', 'CoreTax Scraping', 36, 'Membuka menu Pajak Keluaran...', $this->userId));
                    $driver->findElement(WebDriverBy::linkText("Pajak Keluaran"))->click();
                    break;

                case '2':
                    // click menu id 87
                    broadcast(new UserProgressEvent('info', 'CoreTax Scraping', 36, 'Membuka menu Pajak Masukan...', $this->userId));
                    $driver->findElement(WebDriverBy::linkText("Pajak Masukan"))->click();
                    break;

                default:
                    # code...
                    break;
            }
            // dispatch next job
            // ScrapingJobStep3::dispatch($this->userId, $this->jenisPajak);
        }
    }
}
