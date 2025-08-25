<?php

namespace App\Http\Controllers\Utilities;

use App\Events\UserDataEvent;
use App\Events\UserEvent;
use App\Events\UserProgressEvent;
use App\Http\Controllers\Controller;
use App\Jobs\ScrapingJob;
use App\Models\Captcha;
use App\Models\User;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Intervention\Image\ImageManager;



class SettingController extends Controller
{
    public function usermanIndex()
    {
        return view('settings.userman');
    }

    public function usermanShow(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required'
            ]);
            $user = User::find($request->id);
            return response()->json([
                'status' => true,
                'message' => 'User Found',
                'data' => $user
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function usermanStore(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required',
                'password' => 'required',
                'role' => 'required',
                'depo' => 'required'
            ]);
            $new_user = new User();
            $new_user->name = $request->name;
            $new_user->email = $request->email;
            $new_user->password = bcrypt($request->password);
            $new_user->role = $request->role;
            # $depo is array, so we need to convert it to string

            $new_user->depo = implode("|", $request->depo);
            $new_user->save();
            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'data' => $new_user
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function usermanUpdate(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
                'name' => 'required',
                'email' => 'required',
                'role' => 'required',
                'depo' => 'required'
            ]);
            $user = User::find($request->id);
            $user->name = $request->name;
            $user->email = $request->email;
            if ($request->has('password') && $request->password != '') {
                $user->password = bcrypt($request->password);
            }
            $user->role = $request->role;
            $user->depo = implode("|", $request->depo);
            $user->save();
            return response()->json([
                'status' => true,
                'message' => 'User Updated Successfully',
                'data' => $user
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function usermanDelete(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required'
            ]);
            $user = User::find($request->id);
            $user->delete();
            return response()->json([
                'status' => true,
                'message' => 'User Deleted Successfully',
                'data' => $user
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function usermanChangePassword(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required',
            ]);
            $userId = Auth::user()->id;
            $user = User::find($userId);
            $user->password = bcrypt($request->password);
            $user->save();
            return response()->json([
                'status' => true,
                'message' => 'Password Changed Successfully',
                'data' => $user
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function cobaScraping()
    {
        $host = 'http://localhost:4444/';
        $capabilities = DesiredCapabilities::chrome();
        $driver = RemoteWebDriver::create($host, $capabilities);
        try {
            $driver->get('https://coretaxdjp.pajak.go.id/identityproviderportal/Account/Login');
            $captchaElement = $driver->findElement(WebDriverBy::id('dntCaptchaImg'));

            sleep(5); // wait for the captcha image to load
            $captchaImage = $captchaElement->takeElementScreenshot(public_path('assets/captcha.png'));

            $drv = new Driver();
            $imgManager = new ImageManager($drv);

            $image = $imgManager->read(public_path('assets/captcha.png'));
            $image->greyscale();
            $image->contrast(30);
            $image->brightness(10);
            $image->trim();

            $image->save(public_path('assets/captcha1.png'));

            $result = (new TesseractOCR(public_path('assets/captcha1.png')))->run();

            return response()->json([
                'status' => true,
                'message' => 'Captcha image processed successfully',
                'data' => [
                    'result' => $result,
                ]
            ], 200);
        } catch (\Throwable $th) {
            $driver->quit();
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function coretaxScraping(Request $request)
    {
        $request->validate([
            'jenis_pajak' => 'required',
        ]);

        broadcast(new UserEvent('info', 'Scraping started', 'Memulai proses scraping Coretax.', Auth::user()->id));
        broadcast(new UserProgressEvent('info', 'CoreTax Scraping', 3, 'Scraping CoreTax started...', Auth::user()->id));
        ScrapingJob::dispatch(Auth::user()->id, $request->jenis_pajak);

        return response()->json([
            'status' => true,
            'message' => 'Scraping job dispatched successfully',
        ], 200);
    }

    public function coretaxCaptchaPreview(Request $request)
    {
        try {
            return view('settings.preview-captcha');
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Captcha preview generation failed',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function coretaxCaptcha(Request $request)
    {
        try {
            $request->validate([
                'captcha' => 'required',
            ]);
            $captcha = new Captcha();
            $captcha->captcha = $request->captcha;
            $captcha->user_id = Auth::id();
            $captcha->save();
            return response()->json([
                'status' => true,
                'message' => 'Captcha submitted successfully',
                'data' => [
                    'captcha' => $request->captcha,
                ]
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Captcha submission failed',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function generateCsrfToken()
    {
        try {
            $token = csrf_token();
            return response()->json([
                'status' => true,
                'message' => 'CSRF token generated successfully',
                'data' => [
                    'csrf_token' => $token,
                ]
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'CSRF token generation failed',
                'data' => $th->getMessage()
            ], 500);
        }
    }
}
