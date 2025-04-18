<?php

namespace App\Http\Controllers;

use App\Models\MenuRoleMap;
use Exception;
use Illuminate\Http\Request;

class MenuRoleMapController extends Controller
{

     /**
     * |  Create Menu Role Mapping
     */
    public function createMenuRole(Request $req)
    {  
        try {
            $req->validate([
                'menuId'      => 'required',
                'roleId'      => 'required',
            ]);
            $mMenuRolemap = new MenuRoleMap();
            $checkExisting = $mMenuRolemap->where('menu_id', $req->menuId)
                ->where('role_id', $req->roleId)
                ->where('status', 1)
                ->first();
            
            if ($checkExisting)
                throw new Exception('Menu Already Maps to This Role');

            $mMenuRolemap->addRoleMap($req);

            return responseMsg(true, "Menu attached to the role succesfully", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Menu Role Mapping List
     */
    public function menuRoleList()
    {
        try {
            $mMenuRolemap = new MenuRolemap();
            $menuRole = $mMenuRolemap->listRoleMaps()
                ->where('menu_role_maps.status', 1)
                ->get();

            return responseMsg(true, "Menu Role Map List", $menuRole);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Delete Menu Role Mapping
     */
    public function deleteMenuRole(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);
            $delete = new MenuRoleMap();
            $delete->deleteMenuRole($req);

            return responseMsg(true, "Menu Role Suspended", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Update Menu Role Mapping
     */
    public function updateMenuRole(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);
            $mMenuRolemap = new MenuRoleMap();
            $list  = $mMenuRolemap->updateMenuRole($req);

            return responseMsg(true, "Successfully Updated", $list);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }
}
