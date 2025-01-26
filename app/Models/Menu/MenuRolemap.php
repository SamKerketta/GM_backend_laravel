<?php

namespace App\Models\Menu;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MenuRolemap extends Model
{
    use HasFactory;


    /**
     * Create Role Map
     */
    public function addRoleMap($req)
    {
        $data = new MenuRolemap;
        $data->menu_id      = $req->menuId;
        $data->menu_role_id = $req->menuRoleId;
        $data->is_suspended = $req->isSuspended ?? false;
        $data->save();
    }

    /**
     * Update Role Map
     */
    public function updateRoleMap($req)
    {
        $data = MenuRolemap::find($req->id);
        $data->menu_id      = $req->menuId ?? $data->menu_id;
        $data->menu_role_id = $req->menuRoleId ?? $data->menu_role_id;
        $data->is_suspended = $req->isSuspended ?? $data->is_suspended;
        $data->save();
    }

    /**
     * | Menu Role Map list
     */
    public function roleMaps()
    {
        $data = DB::table('menu_rolemaps')
            ->select('menu_rolemaps.id', 'menu_id', 'menu_role_id', 'menu_rolemaps.is_suspended', 'menu_string', 'route', 'menu_role_name')
            ->leftjoin('menu_masters', 'menu_masters.id', 'menu_rolemaps.menu_id')
            ->join('menu_roles', 'menu_roles.id', 'menu_rolemaps.menu_role_id')
            // ->where('menu_rolemaps.is_suspended', false)
            ->orderByDesc('menu_rolemaps.id');
        return $data;
    }

    /**
     * Delete Role Map
     */
    public function deleteRoleMap($req)
    {
        $data = MenuRolemap::find($req->id);
        $data->is_suspended = true;
        $data->save();
    }
}
