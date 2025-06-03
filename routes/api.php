<?php

use App\Http\Controllers\MasterController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuRoleMapController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
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
    Route::post('login', 'login');                                                                  #_01
    Route::post('register', 'userRegistration');                                                    #_02
    Route::post('forgot-password', 'forgotPassword'); //sendResetLinkEmail                          #_03
    Route::get('reset-password/{token}',  'showResetPasswordForm')->name('reset.password.get');     #_04
    Route::post('validate-password', 'validatePassword');                                           #_05
    Route::post('reset-password', 'resetPassword')->middleware('auth:sanctum');                     #_06
    Route::post('logout', 'logout')->middleware('auth:sanctum');                                    #_07    
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

/**
 * | Created On: 28-04-2025
 * | Created By: Mrinal Kumar
 * | CRUD of Vendor Master || Category Master || Inventory Master || Plan Master
 */
Route::controller(MasterController::class)->group(function () {
    Route::post('crud/vendor/add-vendor', 'createVendor');        #_Adding the details of the Vendors
    Route::post('crud/vendor/list-vendor', 'vendorList');         #_Get All the Vendor
    Route::post('crud/vendor/delete-vendor', 'deleteVendor');     #_Soft Deletion of the Vendor
    Route::post('crud/vendor/update-vendor', 'updateVendor');     #_Update the Vendor

    Route::post('crud/item-category/add-item-category', 'createItemCategory');        #_Adding the details of the Category
    Route::post('crud/item-category/list-item-category', 'ItemCategoryList');         #_Get All the Category
    Route::post('crud/item-category/delete-item-category', 'deleteItemCategory');     #_Soft Deletion of the Category
    Route::post('crud/item-category/update-item-category', 'updateItemCategory');     #_Update the Category

    Route::post('crud/inventor/add-item', 'createItem');        #_Adding the details of the Category
    Route::post('crud/inventor/list-item', 'itemList');         #_Get All the Category
    Route::post('crud/inventor/delete-item', 'deleteItem');     #_Soft Deletion of the Category
    Route::post('crud/inventor/update-item', 'updateItem');     #_Update the Category

    Route::post('crud/plans/add', 'createPlan');        #_Adding the details of the Plans
    Route::post('crud/plans/list', 'planList');         #_Get All the Plans
    Route::post('crud/plans/delete', 'deletePlan');     #_Soft Deletion of the Plans
    Route::post('crud/plans/update', 'updatePlan');     #_Update the Plans
});

/**
 * | Created On: 29-04-2025
 * | Created By: Mrinal Kumar
 * | CRUD of Members
 */
Route::controller(MemberController::class)->group(function () {
    Route::post('crud/member/add-member', 'createMember');        #_Adding the details of the Member
    Route::post('crud/member/list-member', 'memberList');         #_Get All the Member
    Route::post('crud/member/delete-member', 'deleteMember');     #_Soft Deletion of the Member
    Route::post('crud/member/update-member', 'updateMember');     #_Update the Member
    Route::post('crud/member/detail', 'getMemberDetail');         #_Detail of the Member

    Route::post('biometric/logs', 'storeBiometric');             #_Biomertric Logs
    
});

/**
 * | Created On: 30-04-2025
 * | Created By: Mrinal Kumar
 * | For Payment
 */
Route::controller(PaymentController::class)->group(function () {
    Route::post('payment/offline', 'offlinePayment');         #_Offline Payment
    Route::post('payment/receipt', 'paymentReceipt');         #_Payment Receipt
    Route::post('send-whatsapp', 'paymentReminder');          #_Whatsaap Payment Reminder
    Route::post('notifications/whatsapp/payment-success', 'sendWhatsAppPaymentSuccessNotification');          #_Payment Success Notification
    /**
        Under Construction
     */
    Route::post('payment/initiate', 'initiatePayment');        #_
    Route::post('payment/initiate', 'initiatePayment');        #_
});

/**
 * | Created On: 30-04-2025
 * | Created By: Mrinal Kumar
 * | For Reporting
 */
Route::controller(ReportController::class)->group(function () {
    Route::post('report/monthly-payments', 'monthlyPayment');         #_Monthly Payment
    Route::post('report/payment-report', 'paymentReport');            #_Date wise Payment Report
    Route::post('report/plans-expiring', 'expiringPlans');            #_Plans Expiring Report
    Route::post('report/overdue-members', 'fetchMemberDues');         #_Member Dues Report
    Route::post('report/dashboard', 'dasboardReport');                #_Dashboard Report
});
