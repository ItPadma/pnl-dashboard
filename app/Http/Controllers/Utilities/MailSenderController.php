<?php

namespace App\Http\Controllers\Utilities;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Log; // Assuming we want to log actions, similar to LogController

class MailSenderController extends Controller
{
    public function index()
    {
        return view('utilities.mail-sender');
    }

    public function send(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            $to = $request->email;
            $subject = $request->subject;
            $content = $request->message;

            Mail::raw($content, function ($message) use ($to, $subject) {
                $message->to($to)
                        ->subject($subject);
            });

            // Log successful send (optional but good practice based on project structure)
            // LogController::createLog(auth()->id(), 'Send Mail', 'Email', "Sent to $to", 'N/A', 'info', $request);

            return back()->with('success', 'Email sent successfully to ' . $to);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send email: ' . $e->getMessage())->withInput();
        }
    }
}
