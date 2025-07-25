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
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * | User Login
     * | 01
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
                throw new Exception("Account does not exist !!!");
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
     * | 02
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

    /**
     * | Forgot Password 
     * | Sent Link on Mail
     * | 03
     */
    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                // 'email' => 'required|email',
                'email' => 'required|email|exists:users',
            ]);
            if ($validator->fails())
                return validationError($validator);

            $token = Str::random(64);
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);

            Mail::send('email.reset_password', ['token' => $token], function ($message) use ($request) {
                $message->to($request->email);
                $message->subject('Reset Password');
            });

            return responseMsg(true, "We have sent email for password reset link!", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Reset Password Form
     * | 04
     */
    public function showResetPasswordForm($token)
    {
        return view('auth.forgetPasswordLink', ['token' => $token]);
    }

    /**
     * | Validate Password
     * | 05
     */
    public function validatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return validationError($validator);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => __($status)], 200);
        }

        return response()->json(['message' => __($status)], 400);
    }

    /**
     * | Reset Password
     * | 06
     */
    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'oldPassword' => 'required',
                'newPassword' => 'required',
            ]);
            if ($validator->fails())
                return validationError($validator);

            $refUser = auth()->user();

            $refUser = User::find($refUser->id);

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
     * | 07
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

    /**
     * | Update User Details
     * | 08
         Not For Use
     */
    public function updateUser(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                "name"   => 'required',
                "email"  => 'required',
                "mobile" => 'required',
            ]
        );
        if ($validated->fails()) {
            return validationError($validated);
        }
        try {
            $id = auth()->user()->id;
            $user = User::find($id);
            if (!$user)
                throw new Exception("User Not Exist");
            $stmt = $user->email == $request->email;

            if (!$stmt) {
                $check = User::where('email', $request->email)->first();
                if ($check) {
                    throw new Exception('Email Is Already Existing');
                }
            }

            $mUser = new User();
            $mUser->updateUserDetail($request);

            return responseMsgs(true, "Successfully Updated", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }
}
