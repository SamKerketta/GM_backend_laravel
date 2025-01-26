<?php

namespace App\Models\Menu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuMaster extends Model
{
    use HasFactory;

    /**
     * | get All list of Menues form the master table of menues
     */
    public function fetchAllMenues()
    {
        return MenuMaster::select(
            // 'menu_masters.*',
            'menu_masters.id',
            'menu_masters.parent_id',
            'menu_masters.route',
            'menu_masters.icon',
            'menu_masters.menu_string',
            'menu_masters.serial',
            'wf_workflows.alt_name as workflow_name',
            'module_masters.module_name',
            'module_masters.id as module_id',
        )
            ->leftjoin('wf_workflows', 'wf_workflows.id', 'menu_masters.workflow_id')
            ->leftjoin('module_masters', 'module_masters.id', 'menu_masters.module_id')
            ->where('is_deleted', false)
            // ->orderByDesc("id");
            ->orderBy("menu_masters.serial");
    }

    /**
     * | Get Menues By Id
     */
    public function getMenuById($id)
    {
        return MenuMaster::select('menu_masters.*', 'wf_workflows.alt_name as workflow_name')
            ->leftjoin('wf_workflows', 'wf_workflows.id', 'menu_masters.workflow_id')
            ->where('is_deleted', false)
            ->where('menu_masters.id', $id)
            ->firstOrFail();
    }

    /**
     * | Get menu by Role Id
     */
    // public function getMenuByRole($roleId, $moduleId)
    // {
    //     $a = MenuMaster::select(
    //         'menu_masters.id',
    //         'menu_masters.parent_id'
    //     )
    //         ->join('wf_rolemenus', 'wf_rolemenus.menu_id', '=', 'menu_masters.id')
    //         ->where('menu_masters.is_deleted', false)
    //         ->where('wf_rolemenus.status', true)
    //         ->whereIn('wf_rolemenus.role_id', $roleId)
    //         ->where('module_id', $moduleId)         //changes by mrinal and sam
    //         ->orderBy("menu_masters.serial", "Asc")
    //         ->get();
    //     return  objToArray($a);
    // }

    public function getMenuByRole($roleId, $moduleId)
    {
        $a = MenuMaster::select(
            'menu_masters.id',
            'menu_masters.parent_id'
        )
            ->join('menu_rolemaps', 'menu_rolemaps.menu_id', '=', 'menu_masters.id')
            ->where('menu_masters.is_deleted', false)
            ->where('menu_rolemaps.is_suspended', false)
            ->whereIn('menu_rolemaps.menu_role_id', $roleId)
            ->where('module_id', $moduleId)         //changes by mrinal and sam
            ->orderBy("menu_masters.serial", "Asc")
            ->get();
        return  objToArray($a);
    }

    /**
     * | Save Menu
     */
    public function store($request)
    {
        $newMenues = new MenuMaster();
        $newMenues->serial        = $request->serial;
        $newMenues->description   = $request->description;
        $newMenues->menu_string   = $request->menuName;
        $newMenues->parent_id     = $request->parentId ?? 0;
        $newMenues->route         = $request->route;
        $newMenues->icon          = $request->icon;
        $newMenues->module_id     = $request->moduleId;
        $newMenues->workflow_id   = $request->workflowId;
        $newMenues->save();
    }

    /**
     * | Update the menu master details
     */
    public function edit($request)
    {
        $refValues = MenuMaster::where('id', $request->id)->first();
        MenuMaster::where('id', $request->id)
            ->update(
                [
                    'serial'        => $request->serial         ?? $refValues->serial,
                    'description'   => $request->description    ?? $refValues->description,
                    'menu_string'   => $request->menuName       ?? $refValues->menu_string,
                    'parent_id'     => $request->parentId       ?? $refValues->parent_id,
                    'route'         => $request->route          ?? $refValues->route,
                    'icon'          => $request->icon           ?? $refValues->icon,
                    'is_deleted'    => $request->delete         ?? $refValues->is_deleted,
                    'module_id'     => $request->moduleId       ?? $refValues->module_id,
                    'workflow_id'   => $request->workflowId     ?? $refValues->workflow_id,
                ]
            );
    }

    /**
     * | Get Parent Menues
     */
    public function getParentMenue()
    {
        return MenuMaster::select(
            'id',
            'menu_string',
            'parent_id',
            'serial'
        )
            ->where('parent_id', 0)
            ->where('is_deleted', false)
            ->orderBy("menu_masters.serial");
    }
}
