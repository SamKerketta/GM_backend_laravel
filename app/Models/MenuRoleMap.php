<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuRoleMap extends Model
{
    use HasFactory;

     /**
     * | Create Role Map
     */
    public function addRoleMap($req)
    {
        $data = new MenuRolemap;
        $data->menu_id      = $req->menuId;
        $data->role_id      = $req->roleId;
        $data->save();
    }

    /**
     * | Menu Role Map list
     */
    public function listRoleMaps()
    {

         return $data = MenuRoleMap::select('menu_role_maps.id','menu_role_maps.menu_id','menu_role_maps.role_id',
                                                'role_name','menu_name','icon','route')
            ->join('role_masters','role_masters.id','menu_role_maps.role_id')
            ->join('menu_masters','menu_masters.id','menu_role_maps.menu_id');


        #_How many menus are assigned to a one role
        //  return $data = MenuRoleMap::select('menu_role_maps.role_id',
        //     DB::raw("GROUP_CONCAT(menu_masters.menu_name) AS menu_name"),
        //             'role_name')
        //     ->join('role_masters','role_masters.id','menu_role_maps.role_id')
        //     ->join('menu_masters','menu_masters.id','menu_role_maps.menu_id')
        //     ->groupBy('menu_role_maps.role_id');
    }

    /**
     * | Delete Menu Role Map
     */
    public function deleteMenuRole($req)
    {
        $data = MenuRoleMap::find($req->id);
        $data->status = false;
        $data->save();
    }

    /**
     * Update Menu Role Map
     */
    public function updateMenuRole($req)
    {
        $data = MenuRoleMap::find($req->id);
        $data->menu_id = $req->menuId ?? $data->menu_id;
        $data->role_id = $req->roleId ?? $data->role_id;
        $data->status  = $req->status ?? $data->status;
        $data->save();
    }
}
