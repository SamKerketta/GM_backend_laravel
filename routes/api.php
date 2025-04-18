<?php

use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuRoleMapController;
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
 * | Created On-18-04-2025
 * | Created By- Mrinal Kumar
 * | Menu Permissions
 */
Route::controller(MenuController::class)->group(function () {
    Route::post('crud/menu/add-new-menu', 'addNewMenus');            #_Adding the details of the menus in the menu table
    Route::post('crud/menu/get-all-menu', 'fetchAllMenus');          #_Get All the Menu List
    Route::post('crud/menu/delete-menu', 'deleteMenu');              #_Soft Deletion of the menus
    Route::post('crud/menu/update-menu', 'updateMenu');              #_Update the menu master 
    Route::post('menu/get-menu-by-id', 'getMenuById');               #_Get menu bu menu Id

    /**
        Under Construction
     */
    Route::post('menu-roles/get-menu-by-roles', 'getMenuByroles');              #_Get all the menu by roles
    Route::post('menu-roles/update-menu-by-role', 'updateMenuByRole');          #_Update Menu Permission By Role
    Route::post('menu-roles/list-parent-serial', 'listParentSerial');           #_Get the list of parent menus

    Route::post('sub-menu/tree-structure', 'getTreeStructureMenu');             #_Generation of the menu tree Structure        
    Route::post('sub-menu/get-children-node', 'getChildrenNode');               #_Get the children menus
});

/**
 * | Created On-19-04-2025. 12 baje k baad
 * | Created By- Mrinal Kumar
 * | Menu Role Mapping
 */
Route::controller(MenuRoleMapController::class)->group(function () {
    Route::post('crud/menu/add-menu-role', 'createMenuRole');        #_Adding the details of the menu role maps
    Route::post('crud/menu/list-menu-role', 'menuRoleList');         #_Get All the Menu Role List
    Route::post('crud/menu/delete-menu-role', 'deleteMenuRole');     #_Soft Deletion of the menu role map
    Route::post('crud/menu/update-menu-role', 'updateMenuRole');     #_Update the menu role maps 
    /**
        Under Construction
     */
    Route::post('menu/get-menu-by-id', 'getMenuById');               #_Get menu by menu Id
});
