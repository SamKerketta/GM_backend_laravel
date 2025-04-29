<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * | Add Item Details
     */
    public function addItem($req)
    {
        $mInventory = new Inventory();
        $mInventory->create($req);
    }

    /**
     * | Edit Item Inventory Details
     */
    public function editItem($req)
    {
        $mInventory = Inventory::findorfail($req['id']);
        $mInventory->update($req);
    }

    /**
     * | Fetch Item Inventory List
     */
    public function fetchItem()
    {
       return Inventory::where('status',1)
                        ->orderBy('item_name')
                        ->get();
    }
}
