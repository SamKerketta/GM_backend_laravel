<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Models\Menu\MenuRole;
use App\Models\Menu\MenuRoleusermap;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MenuRoleUserMapController extends Controller
{
    /**
     * | Create Menu Role Mapping
     */
    public function createRoleUser(Request $req)
    {
        try {
            $req->validate([
                'userId'     => 'required',
                'menuRoleId' => 'required',
                'permissionStatus' => 'required'
            ]);
            $mMenuRoleusermap = new MenuRoleusermap();
            $checkExisting =  $mMenuRoleusermap->where('menu_role_id', $req->menuRoleId)
                ->where('user_id', $req->userId)
                ->first();

            if ($req->permissionStatus == 1)
                $isSuspended = false;
            if ($req->permissionStatus == 0)
                $isSuspended = true;


            if ($checkExisting) {
                $req->merge([
                    'id' => $checkExisting->id,
                    'isSuspended' => $isSuspended
                ]);
                $mMenuRoleusermap->updateRoleUser($req);
            } else {
                $req->merge([
                    'isSuspended' => $isSuspended,
                ]);
                $mMenuRoleusermap->addRoleUser($req);
            }

            return responseMsgs(true, "Data Saved", "", "120901", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120901", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    /**
     * | Update Menu Role Mapping
     */
    public function updateRoleUser(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|integer'
        ]);
        if ($validator->fails())
            return validationError($validator);

        try {
            $mMenuRoleusermap = new MenuRoleusermap();
            $mMenuRoleusermap->updateRoleUser($req);

            return responseMsgs(true, "Data Updated", [], "120902", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120902", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    /**
     * | Menu Role Mapping By id
     */
    public function roleUserbyId(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);

            $mMenuRoleusermap = new MenuRoleusermap();
            $list  = $mMenuRoleusermap->listRoleUser($req)
                ->where('menu_roleusermaps.id', $req->id)
                ->first();

            return responseMsgs(true, "Menu Role Map", remove_null($list), "120903", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120903", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }


    /**
     * | Menu Role Mapping List
     */
    public function getAllRoleUser(Request $req)
    {
        try {
            $mMenuRoleusermap = new MenuRoleusermap();
            $menuRole = $mMenuRoleusermap->listRoleUser()->get();

            return responseMsgs(true, "Menu Role Map List", $menuRole, "120904", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120904", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    /**
     * | Delete Menu Role Mapping
     */
    public function deleteRoleUser(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);
            $delete = new MenuRoleusermap();
            $delete->deleteRoleUser($req);

            return responseMsgs(true, "Data Deleted", '', "120905", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120905", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    /**
     * | Roles by User Id
     */
    // public function roleByUserId(Request $req)
    // {
    //     try {
    //         $validator = Validator::make($req->all(), [
    //             'userId' => 'required|integer'
    //         ]);
    //         if ($validator->fails())
    //             return validationError($validator);

    //         $mMenuRoleusermap = new MenuRoleusermap;
    //         $menuRole = new MenuRole();
    //         $menuRoleList = collect();

    //         $menuRoleDtl = $mMenuRoleusermap->listRoleUser()->get();

    //         $query = "select 
    //                         m.id,
    //                         m.menu_role_name,
    //                         mr.user_id,
    //                         case 
    //                             when mr.user_id is null then false
    //                             else
    //                                 true  
    //                         end as permission_status

    //                     from menu_roles as m
    //                     left join (select * from menu_roleusermaps where user_id=$req->userId and is_suspended=false) as mr on mr.menu_role_id=m.id
    //                     order by m.id";

    //         $data = DB::select($query);

    //         // $data = $mMenuRoleusermap->getRoleByUserId()
    //         // ->where('menu_roleusermaps.user_id', '=', $req->userId)
    //         // ->get();

    //         return responseMsgs(true, 'Menu Role Map By User Id', $data, "120907", "1.0", responseTime(), "POST", $req->deviceId);
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "120907", "1.0", responseTime(), "POST", $req->deviceId);
    //     }
    // }

    //Roles Except Given user id
    // public function roleExcludingUserId(Request $req)
    // {
    //     try {
    //         $validator = Validator::make($req->all(), [
    //             'userId' => 'required|integer'
    //         ]);
    //         if ($validator->fails())
    //             return validationError($validator);

    //         $menuRoleUserMap = new MenuRoleusermap;
    //         $mMenuRole = new MenuRole();
    //         $menuRole = $menuRoleUserMap->getRoleByUserId()
    //             ->where('menu_roleusermaps.user_id', $req->userId)
    //             ->get();

    //         $menuRoleId = $menuRole->pluck('menu_role_id');

    //         $menuRole = $mMenuRole->listMenuRole()
    //             ->whereNotIn('menu_roles.id', $menuRoleId)
    //             ->get();

    //         return responseMsgs(true, 'Menu Role Map Except User Id', $menuRole, "120908", "1.0", responseTime(), "POST", $req->deviceId);
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "120908", "1.0", responseTime(), "POST", $req->deviceId);
    //     }
    // }
}
