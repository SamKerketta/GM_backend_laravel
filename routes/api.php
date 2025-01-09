<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * | User Register & Login
 */
Route::controller(UserController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'userRegistration');
    Route::post('forgot-password', 'forgotPassword'); //sendResetLinkEmail
    Route::post('validate-password', 'validatePassword');
    Route::post('reset-password', 'resetPassword')->middleware('auth:sanctum');
    Route::post('logout', 'logout')->middleware('auth:sanctum');

    Route::get('reset-password/{token}',  'showResetPasswordForm')->name('reset.password.get');
    Route::post('reset-password',  'submitResetPasswordForm')->name('reset.password.post');
});
