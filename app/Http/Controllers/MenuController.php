<?php

namespace App\Http\Controllers;
use App\Models\Menu\MenuMaster;
use Exception;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * | Add Data of Menu in Menu Master
     */
    public function addNewMenus(Request $request)
    {
        try {
            $request->validate([
                'menuName'      => 'required',
                'route'         => 'nullable',
            ]);
            $mMenuMaster = new MenuMaster();
            $mMenuMaster->putNewMenues($request);
            return responseMsg(true, "Data Saved!", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Fetch all menu list
     */
    public function fetchAllMenus(Request $request)
    {
        try {
            $mMenuMaster  = new MenuMaster();
            $refmenus     = $mMenuMaster->fetchAllMenus();
            $menus        = $refmenus->sortByDesc("id");
            $listedMenues = collect($menus)->map(function ($value) use ($mMenuMaster) {
                if ($value['parent_serial'] != 0) {
                    $parent = $mMenuMaster->getMenuById($value['parent_serial']);
                    $parentName = $parent['menu_string'];
                    $value['parentName'] = $parentName;
                    return $value;
                }
                return $value;
            })->values();
            return responseMsg(true, "List of Menus", $listedMenues);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Soft Deletion of the Menu
     */
    public function deleteMenu(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required'
            ]);
            $menuDeletion = new MenuMaster();
            $menuDeletion->softDeleteMenus($request->id);
            return responseMsg(true, "Menu Deleted Succesfully", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Update Menu
     */
    public function updateMenu(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'serial' => 'nullable|int',
            'route'  => 'nullable',
            'status' => 'nullable|boolean'
        ]);
        try {
            $mMenuMaster = new MenuMaster();
            $mMenuMaster->updateMenuMaster($request);
            return responseMsg(true, "Menu Updated!", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * |--------------------- Get the child of the menu  ---------------------|
     * | @param request
     * | @var mMenuMaster Model 
     * | @var listedChild List of chil nodes
     * | @return listedChild 
        | Serial No : 08
        | Closed
     */
    public function getChildrenNode(Request $request)
    {
        try {
            $mMenuMaster = new MenuMaster();
            $listedChild = $mMenuMaster->getChildrenNode($request->id)->get();
            return responseMsgs(true, "child Menu!", $listedChild, "", "", "", "POST", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }


    /**
     * |--------------------- Update menu Master ---------------------|
     * | @param request
     * | @var mMenuMaster Model
        | Serial No : 09
        | Closed
     */
    public function updateMenuMaster(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'serial' => 'nullable|int',
            'parentSerial' => 'nullable|int',
            'route' => 'nullable|',
            'delete' => 'nullable|boolean'
        ]);
        try {
            $mMenuMaster = new MenuMaster();
            $mMenuMaster->updateMenuMaster($request);
            return responseMsgs(true, "Menu Updated!", "", "", "02", "733", "POST", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }


    /**
     * | Get menu by Menu Id
     */
    public function getMenuById(Request $request)
    {
        $request->validate([
            'menuId' => 'required|int'
        ]);
        try {
            $mMenuMaster = new MenuMaster();
            $menus = $mMenuMaster->getMenuById($request->menuId);
            if ($menus['parent_serial'] == 0) {
                return responseMsg(true, "Menu List!", $menus);
            }
            $parent = $mMenuMaster->getMenuById($menus['parent_serial']);
            $menus['parentName'] = $parent['menu_string'];
            return responseMsg(true, "Menu List!", $menus);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }
}
