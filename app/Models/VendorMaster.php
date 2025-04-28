<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorMaster extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * | Add Vendor Details
     */
    public function addVendor($req)
    {
        $mVendorMaster = new VendorMaster();
        $mVendorMaster->create($req);
    }

    /**
     * | Edit Vendor Details
     */
    public function editVendor($req)
    {
        $mVendorMaster = VendorMaster::find($req['id']);
        $mVendorMaster->update($req);
    }

    /**
     * | Fetch Vendor List
     */
    public function fetchvendors()
    {
       return VendorMaster::where('status',1)
                        ->orderBy('vendor_name')
                        ->get();
    }
}
