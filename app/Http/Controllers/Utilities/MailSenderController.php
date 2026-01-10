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
            'attachments.*' => 'file|max:10240', // Max 10MB per file
        ]);

        try {
            $to = $request->email;
            $subject = $request->subject;
            $content = $request->message;
            $attachments = $request->file('attachments');

            Mail::raw($content, function ($message) use ($to, $subject, $attachments) {
                $message->to($to)
                        ->subject($subject);

                if ($attachments) {
                    foreach ($attachments as $file) {
                        $message->attach($file->getRealPath(), [
                            'as' => $file->getClientOriginalName(),
                            'mime' => $file->getClientMimeType(),
                        ]);
                    }
                }
            });

            // Log successful send (optional)
            // LogController::createLog(auth()->id(), 'Send Mail', 'Email', "Sent to $to", 'N/A', 'info', $request);

            if ($request->ajax()) {
                return response()->json(['message' => 'Email sent successfully to ' . $to, 'status' => 'success']);
            }

            return back()->with('success', 'Email sent successfully to ' . $to);
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Failed to send email: ' . $e->getMessage(), 'status' => 'error'], 500);
            }
            return back()->with('error', 'Failed to send email: ' . $e->getMessage())->withInput();
        }
    }
}
