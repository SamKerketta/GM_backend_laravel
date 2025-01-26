<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Models\Menu\MenuRolemap;
use Exception;
use Illuminate\Http\Request;

class MenuRoleMapController extends Controller
{
    /**
     * |  Create Menu Role Mapping
     */
    public function createRoleMap(Request $req)
    {
        try {
            $req->validate([
                'menuId'     => 'required',
                'menuRoleId' => 'required',
                'isSuspended' => 'nullable|boolean'

            ]);
            $mMenuRolemap = new MenuRolemap();
            $checkExisting = $mMenuRolemap->where('menu_id', $req->menuId)
                ->where('menu_role_id', $req->menuRoleId)
                ->first();

            if ($checkExisting) {
                $req->merge([
                    'id' => $checkExisting->id,
                    'isSuspended' => $req->isSuspended
                ]);
                $mMenuRolemap->updateRoleMap($req);
            } else {
                $mMenuRolemap->addRoleMap($req);
            }

            // if ($checkExisting)
            //     throw new Exception('Menu Already Maps to Menu Role');
            // $mreqs = [
            //     'menuId'     => $req->menuId,
            //     'menuRoleId' => $req->menuRoleId
            // ];
            // $mMenuRolemap->addRoleMap($mreqs);

            return responseMsg(true, "Data Saved", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Update Menu Role Mapping
     */
    public function updateRoleMap(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);
            $mMenuRolemap = new MenuRolemap();
            $list  = $mMenuRolemap->updateRoleMap($req);

            return responseMsg(true, "Successfully Updated", $list);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Menu Role Mapping By id
     */
    public function roleMapbyId(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);

            $mMenuRolemap = new MenuRolemap();
            $list  = $mMenuRolemap->roleMaps($req)
                ->where('menu_rolemaps.id', $req->id)
                ->where('menu_rolemaps.is_suspended', false)
                ->first();

            return responseMsg(true, "Menu Role Map", remove_null($list));
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }


    /**
     * | Menu Role Mapping List
     */
    public function getAllRoleMap()
    {
        try {
            $mMenuRolemap = new MenuRolemap();
            $menuRole = $mMenuRolemap->roleMaps()
                ->where('menu_rolemaps.is_suspended', false)
                ->get();

            return responseMsg(true, "Menu Role Map List", $menuRole);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Delete Menu Role Mapping
     */
    public function deleteRoleMap(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);
            $delete = new MenuRolemap();
            $delete->deleteRoleMap($req);

            return responseMsg(true, "Data Deleted", '');
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }
}
