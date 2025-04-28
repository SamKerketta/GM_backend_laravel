<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemCategoryMaster extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * | Add Item Category Details
     */
    public function addItemCategory($req)
    {
        $mItemCategoryMaster = new ItemCategoryMaster();
        $mItemCategoryMaster->create($req);
    }

    /**
     * | Edit Item Category Details
     */
    public function editItemCategory($req)
    {
        $mVendorMaster = ItemCategoryMaster::find($req['id']);
        $mVendorMaster->update($req);
    }

    /**
     * | Fetch Item Category List
     */
    public function fetchItemCategory()
    {
       return ItemCategoryMaster::where('status',1)
                        ->orderBy('category_name')
                        ->get();
    }
}
