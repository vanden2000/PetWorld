<?php

namespace App\Services;

use App\Mail\ForgotPasswordOtpMail;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordResetOtpService
{
    public function send(User $user): void
    {
        $otp = sprintf('%06d', random_int(100000, 999999));

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($otp),
                'created_at' => Carbon::now(),
            ]
        );

        Mail::to($user->email)->send(new ForgotPasswordOtpMail($otp, $user->name));
    }
}
