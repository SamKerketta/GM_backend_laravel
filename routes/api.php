<?php

use App\Http\Controllers\MenuController;
use App\Http\Controllers\RoleController;
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

/**
 * | Menu Management 
 */
Route::controller(MenuController::class)->group(function () {
    Route::post('save_menu', 'saveMenu');
    Route::post('upade_menu', 'updateMenu');
    Route::post('delete_menu', 'deleteMenu');

    Route::post('_menu', 'saveMenu');
});

/**
 * | Role Management
 */
Route::controller(RoleController::class)->group(function () {
    Route::post('save_role', 'saveRole');
    Route::post('upade_menu', 'saveMenu');
    Route::post('delete_menu', 'saveMenu');

    Route::post('_menu', 'saveMenu');
});


// Menu controller
Route::controller(MenuController::class)->group(function () {
    Route::post('user-managment/v1/crud/menu/save', 'createMenu');
    Route::post('user-managment/v1/crud/menu/edit', 'updateMenu');
    Route::post('user-managment/v1/crud/menu/delete', 'deleteMenu');
    Route::post('user-managment/v1/crud/menu/get', 'getMenuById');
    Route::post('user-managment/v1/crud/menu/list', 'menuList');
    Route::post('user-managment/v1/crud/module/list', 'moduleList')->withoutMiddleware('auth:sanctum');
    Route::post('user-managment/v2/crud/module/list', 'moduleListV2')->withoutMiddleware('auth:sanctum');
    Route::post('user-managment/v1/crud/menu/list-parent-serial', 'listParentSerial');

    Route::post('menu-roles/update-menu-by-role', 'updateMenuByRole');
    Route::post('menu/get-menu-by-roles', 'getMenuByRoles');
    Route::post('menu/by-module', 'getMenuByModuleId');
    Route::post('sub-menu/get-children-node', 'getChildrenNode');
    Route::post('sub-menu/tree-structure', 'getTreeStructureMenu');
});
