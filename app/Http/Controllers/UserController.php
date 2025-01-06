<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
}
