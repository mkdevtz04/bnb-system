<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class OtpAuthController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $email = $request->email;

        // Generate a 4 digit code
        $code = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

        // Save to cache for 10 minutes
        Cache::put('otp_' . $email, $code, now()->addMinutes(10));

        // Create user if not exists
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Guest User - ' . Str::random(5),
                'password' => bcrypt(Str::random(16)) // Random inaccessible password
            ]
        );

        // Send Email
        Mail::to($email)->send(new OtpMail($code));

        return response()->json(['success' => true, 'message' => 'OTP sent successfully']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:4'
        ]);

        $cachedCode = Cache::get('otp_' . $request->email);

        if ($cachedCode === null || $cachedCode !== $request->code) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP'], 401);
        }

        // Clear cache
        Cache::forget('otp_' . $request->email);

        // Login user
        $user = User::where('email', $request->email)->first();
        if (!$user) {
             return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        Auth::login($user);
        $request->session()->regenerate();

        $redirect = $user->isAdmin() ? route('admin.dashboard') : route('dashboard');

        return response()->json(['success' => true, 'redirect' => $redirect]);
    }
}
