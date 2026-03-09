<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OtpController extends Controller
{
    /**
     * Send OTP to the user's email
     */
    public function send(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $code = rand(1000, 9999);
        
        // In a real app, send this via Mail
        session(['otp_code' => $code, 'otp_email' => $request->email]);
        
        Log::info("OTP Code for {$request->email}: {$code}");

        return response()->json([
            'success' => true,
            'message' => 'Code sent successfully! Check your inbox (or logs).',
            'code_hint' => $code // For demo purposes
        ]);
    }

    /**
     * Verify OTP and login/register user
     */
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:4',
        ]);

        $storedCode = session('otp_code');
        $storedEmail = session('otp_email');

        if ($request->code == $storedCode && $request->email == $storedEmail) {
            // Success!
            $user = User::firstOrCreate(
                ['email' => $request->email],
                ['name' => strstr($request->email, '@', true), 'password' => bcrypt(Str::random(16))]
            );

            Auth::login($user);
            
            // Clear session
            session()->forget(['otp_code', 'otp_email']);

            return response()->json([
                'success' => true,
                'redirect' => $user->role === 'admin' ? route('admin.dashboard') : route('dashboard')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired code.'
        ], 422);
    }
}
