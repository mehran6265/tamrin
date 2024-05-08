<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ForgotPassword;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;


class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    // use SendsPasswordResetEmails;


    /**
     * Write code on Method
     *
     * @return response()
     */

    public function showForgetPasswordForm()
    {
        return view('auth.forgetPassword', ["language" => "en"]);
    }

    /**
     * Write code on Method
     *
     * @return response()
     */

    public function submitForgetPasswordForm(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        DB::table('password_resets')->where(['email' => $request->email])->delete();

        $token = Str::random(64);

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        // $details = [
        //     'token' => $token
        // ];


        //Mail::to($request->email)->send(new ForgotPassword($details));




$details = [
            'title' => "Beste ZPC-er,",
            'body1' => "Klik hieronder om jouw wachtwoord opnieuw in te stellen.",
            'token' => $token,
            'body3' => "Met vriendelijke groet,",
            'body4' => "Team ZPC",
        ];
\Mail::send((new \App\Mail\ForgotPassword($details))
    ->to($request->email)->subject("Wachtwoord herstellen!"));












        return back()->with(['message' => "We have e-mailed your password reset link!"]);
    }

    /**
     * Write code on Method
     *
     * @return response()
     */

    public function showResetPasswordForm($language, $token)
    {
        try {
            $token_exists = DB::table('password_resets')->where([
                'token' => $token
            ])->exists();

            if ($token_exists) {
                return view('auth.forgetPasswordLink')->with(['token' => $token, "language" => $language]);
            } else {
                return view('auth.forgetPasswordLink')->with(['token' => "expired", "language" => $language]);
            }
        } catch (Exception $e) {
            return view('auth.forgetPasswordLink')->with(['token' => "expired", "language" => $language]);
        }
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function submitResetPasswordForm(Request $request)
    {
        $request->validate([
            'token' => 'required|string|exists:password_resets',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required'
        ]);

        $updatePassword = DB::table('password_resets')->where([
            'token' => $request->token
        ])->first();

        if (!$updatePassword) {
            return back()->withInput()->withErrors("This link is expired!");
        }

        $user = User::where('email', $updatePassword->email)->update(['password' => Hash::make($request->password)]);

        DB::table('password_resets')->where(['email' => $updatePassword->email])->delete();

        return redirect()->route("login", ["language" => $request->language])->with('message', "Your password has been changed successfully!");
    }
}
