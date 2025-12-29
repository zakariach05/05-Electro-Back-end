<?php

namespace App\Http\Controllers;

use App\Models\MagicLink;
use App\Models\User;
use App\Mail\MagicLinkMail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MagicLinkController extends Controller
{
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $email = $request->input('email');
        $token = Str::random(64);

        $expires = Carbon::now()->addMinutes(10);

        MagicLink::create([
            'email' => $email,
            'token' => $token,
            'expires_at' => $expires,
        ]);

        $frontend = env('FRONTEND_URL', 'http://localhost:3000');
        $link = $frontend . '/magic-login?token=' . $token;

        try {
            Mail::to($email)->send(new MagicLinkMail($link));
        } catch (\Exception $e) {
            // Don't expose internal error to client
            \Log::error('Magic link mail error: ' . $e->getMessage());
        }

        return response()->json(['message' => 'Magic link sent. Check your email.']);
    }

    public function verify(Request $request)
    {
        $token = $request->input('token');
        if (!$token) return response()->json(['error' => 'Token required'], 422);

        $magic = MagicLink::where('token', $token)->first();
        if (!$magic) return response()->json(['error' => 'Invalid or expired token'], 400);

        if ($magic->used_at) return response()->json(['error' => 'Token already used'], 400);
        if (Carbon::now()->gt(Carbon::parse($magic->expires_at))) return response()->json(['error' => 'Token expired'], 400);

        // Mark used
        $magic->used_at = Carbon::now();
        $magic->save();

        // Find or create user
        $user = User::firstWhere('email', $magic->email);
        if (!$user) {
            $user = User::create(['email' => $magic->email, 'name' => explode('@', $magic->email)[0]]);
        }

        // Create token using Sanctum
        $plain = $user->createToken('magic-link')->plainTextToken;

        return response()->json(['token' => $plain, 'user' => $user]);
    }
}
