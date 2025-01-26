<?php

namespace App\Models\Menu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuRole extends Model
{
    use HasFactory;
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * | Save Menu
     */
    public function store($request)
    {
        $newMenues = new MenuRole();
        $newMenues->menu_role_name  =  $request->menuRoleName;
        $newMenues->created_by      =  authUser()->id;
        $newMenues->save();
        return $newMenues;
    }

    /**
     * | Update the menu master details
     */
    public function edit($request)
    {
        $refValues = MenuRole::where('id', $request->id)->first();
        MenuRole::where('id', $request->id)
            ->update(
                [
                    'menu_role_name' => $request->menuRoleName ?? $refValues->menu_role_name,
                ]
            );
    }

    /**
     * | List of Menu Role
     */
    public function listMenuRole()
    {
        return $mMenuRole = MenuRole::select('menu_roles.id', 'menu_role_name', 'is_suspended', 'users.name as created_by')
            ->join('users', 'users.id', 'menu_roles.created_by')
            ->where('menu_roles.is_suspended', false)
            ->orderbydesc('id');
    }
}
