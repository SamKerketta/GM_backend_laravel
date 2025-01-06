<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Mail\SendCodeResetPassword;

class UserController extends Controller
{
    /**
     * | User Login
     */
    public function login(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
                'password' => 'required',
                'type' => "nullable|in:mobile"
            ]
        );
        if ($validated->fails())
            return validationError($validated);
        try {
            $mUser = new User();
            $user  = $mUser->getUserByEmail($request->email);
            if (!$user)
                throw new Exception("Please enter a valid email.");
            if ($user->suspended == true)
                throw new Exception("You are not authorized to log in!");
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('my-app-token')->plainTextToken;

                $data['token'] = $token;
                $data['userDetails'] = $user;
                return responseMsg(true, "You have Logged In Successfully", $data);
            }

            throw new Exception("Invalid Credentials");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | User Registration
     */
    public function userRegistration(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'name'     => 'required|regex:/^[a-zA-Z0-9\s]+$/',
                'mobile'   => 'required|regex:/^[0-9]{10}$/',
                'email'    => 'required|email',
                'password' => 'required|min:5',
            ]
        );
        if ($validated->fails())
            return validationError($validated);
        try {
            $user = new User;
            $user->name     = $request->name;
            $user->mobile   = $request->mobile;
            $user->email    = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();
            return responseMsg(true, "User Registered Successfully !! Please Continue to Login", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    // forget => email =>link. Link ka expiration time will expire. code step by settype

    /**
             Forgot Password
     */
    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users',
            ]);
            if ($validator->fails())
                return validationError($validator);

            // Delete all old code that the user sent before.
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            $token = Str::random(64);

            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);
            Mail::to($request->email)->send(new SendCodeResetPassword($token));

            return responseMsg(true, "We have sent email for password reset link!", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Reset Password
     */
    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email'       => 'required',
                'oldPassword' => 'required',
                'newPassword' => 'required',
            ]);
            if ($validator->fails())
                return validationError($validator);

            $refUser = User::where('email', $request->email)
                ->first();

            // If the User is existing
            if ($refUser) {
                // Checking Password
                if (Hash::check($request->password, $refUser->password)) {
                    $refUser->password = Hash::make($request->newPassword);
                    $refUser->save();

                    return responseMsg(true, "Password changed successfully", "");
                }

                // If Password Does not Matched
                else
                    return responseMsg(false, "Password not matched", "");
            }
            // If the UserName is not Existing
            else
                return responseMsg(false, "User not found", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Logout
     */
    public function logout(Request $request)
    {
        try {
            $token = $request->user()->currentAccessToken();
            $token->expires_at = Carbon::now();
            $token->save();
            return responseMsg(true, "You have Logged Out", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }
}
