<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Models\Menu\MenuMaster;
use App\Models\Menu\MenuRole;
use App\Models\Menu\MenuRolemap;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuRoleController extends Controller
{
    /**
     * | Save Menu Role
     */
    public function createMenuRole(Request $request)
    {
        try {
            $request->validate([
                'menuRoleName' => 'required',
                // 'moduleId'     => 'required|integer'
            ]);
            $mMenuRolemap = new MenuRolemap();
            $mMenuRole = new MenuRole();
            $mMenuMaster  = new MenuMaster();
            $menuRole = $mMenuRole->store($request);

            $menuRoleId = $menuRole->id;
            // if ($request->moduleId) {
            // $menus = $mMenuMaster->fetchAllMenues()
            //     // ->where('module_id', $request->moduleId)
            //     ->get();
            // foreach ($menus as $menu) {
            //     $data['menuId']      = $menu->id;
            //     $data['menuRoleId']  = $menuRoleId;
            //     $data['isSuspended'] = true;

            //     //Menu Role Mapping at the time of Role Creation.
            //     $mMenuRolemap->addRoleMap($data);
            // }
            // }

            return responseMsgs(true, "Data Saved!", "", "", "02", "", "POST", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Update Menu Role
     */
    public function updateMenuRole(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
                'menuRoleName' => 'required',
            ]);
            $mMenuRole = new MenuRole();
            $mMenuRole->edit($request);
            return responseMsgs(true, "Menu Role Updated!", "", "", "02", "733", "POST", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Delete Menu Role
     */
    public function deleteMenuRole(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required'
            ]);
            MenuRole::where('id', $request->id)
                ->update(['is_suspended' => true]);
            return responseMsgs(true, "Menu Role Deleted!", "", "", "02", "733", "POST", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Menu Role by Id
     */
    public function getMenuRole(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|int'
            ]);
            $mMenuRole = new MenuRole();
            $list = $mMenuRole->listMenuRole()
                ->where('menu_roles.id', $request->id)
                ->first();
            // $mMenuRole = MenuRole::find($request->id);

            return responseMsgs(true, "Menu Role!", $list, "", "01", "", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", "", "POST", "");
        }
    }

    /**
     * | Menu Role List
     */
    public function listMenuRole(Request $request)
    {
        try {
            $mMenuRole = new MenuRole();
            $list = $mMenuRole->listMenuRole()
                ->get();

            return responseMsgs(true, "List of Menu Role!", $list, "", "01", "", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", "", "POST", "");
        }
    }

    /**
     * | Menu Role Mapping List By Menu Role Id
     */
    public function menuByMenuRole(Request $req)
    {
        try {
            $req->validate([
                'menuRoleId' => 'required'
            ]);
            $mMenuRolemap = new MenuRolemap();
            // $menuRole = $mMenuRolemap->roleMaps()
            //     ->where('menu_rolemaps.menu_role_id', $req->menuRoleId)
            //     ->get();

            $query = "select 
                            m.id,
                            m.menu_string,
                            mr.menu_role_id,
                            module_masters.module_name,
                            case 
                                when mr.menu_role_id is null then true
                                else
                                    false  
                            end as is_suspended
                    
                        from menu_masters as m
                        left join (select * from menu_rolemaps where menu_role_id=$req->menuRoleId and is_suspended = false) as mr on mr.menu_id=m.id
                        join module_masters on module_masters.id = m.module_id
                        order by m.id";

            $data = DB::select($query);

            return responseMsg(true, "Menu Role Map List", $data);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }
}
