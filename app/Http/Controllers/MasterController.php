<?php

namespace App\Http\Controllers;

use App\Models\ItemCategoryMaster;
use App\Models\VendorMaster;
use Exception;
use Illuminate\Http\Request;

class MasterController extends Controller
{

    /**
     * ================== CRUD OF VENDORS ======================
     */
    /**
     * | Add Data of Vendor in Vendor Master
     */
    public function createVendor(Request $request)
    {
        try {
            $request->validate([
                'vendorName'      => 'required',
                'vendorAddress'   => 'nullable',
            ]);
            $mVendorMaster = new VendorMaster();
            $mreqs = [
                "vendor_name"    => $request->vendorName,
                "vendor_address" => $request->vendorAddress
            ];
            $mVendorMaster->addVendor($mreqs);    
            return responseMsg(true, "Vendor Added Succesfully", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Fetch all vendor list
     */
    public function vendorList(Request $request)
    {
        try {
            $mVendorMaster = new VendorMaster();
            $vendorList    = $mVendorMaster->fetchvendors();

            return responseMsg(true, "List of Vendors", $vendorList);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Soft Deletion of the Vendor
     */
    public function deleteVendor(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required'
            ]);
            $vendorDeletion = new VendorMaster();
            $vendorDeletion->where('id', $request->id)->update(['status' => '0']);
            return responseMsg(true, "Vendor Deleted Succesfully", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Update Vendor Details
     */
    public function updateVendor(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'vendorName'    => 'required',
            'vendorAddress' => 'required',
            'status'        => 'required|boolean'
        ]);
        try {
            $mVendorMaster = new VendorMaster();
            $mreqs = 
            [
                'id'             => $request->id,
                'vendor_name'    => $request->vendorName,
                'vendor_address' => $request->vendorAddress,
                'status'         => $request->status
            ];
            $mVendorMaster->editVendor($mreqs);
            return responseMsg(true, "Vendor Details Updated", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * ================== CRUD OF ITEM CATEGORY ======================
     */

    /**
     * | Add Data of Item category in Item Category Master
     */
    public function createItemCategory(Request $request)
    {
        try {
            $request->validate([
                'categoryName'      => 'required',
            ]);
            $mItemCategoryMaster = new ItemCategoryMaster();
            $mreqs = [
                "category_name"    => $request->categoryName,
            ];
            $mItemCategoryMaster->addItemCategory($mreqs);    
            return responseMsg(true, "Item Category Added Succesfully", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Fetch all Item category
     */
    public function ItemCategoryList(Request $request)
    {
        try {
            $mItemCategoryMaster = new ItemCategoryMaster();
            $categoryList        = $mItemCategoryMaster->fetchItemCategory();

            return responseMsg(true, "List of Item Category", $categoryList);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Soft Deletion of the Item category
     */
    public function deleteItemCategory(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required'
            ]);
            $mItemCategoryMaster = new ItemCategoryMaster();
            $mItemCategoryMaster->where('id', $request->id)->update(['status' => '0']);
            return responseMsg(true, "Item Category Deleted Succesfully", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Update Item category Details
     */
    public function updateItemCategory(Request $request)
    {
        $request->validate([
            'id'            => 'required',
            'categoryName'  => 'required',
            'status'        => 'required|boolean'
        ]);
        try {
            $mItemCategoryMaster = new ItemCategoryMaster();
            $mreqs = 
            [
                'id'             => $request->id,
                'category_name'  => $request->categoryName,
                'status'         => $request->status
            ];
            $mItemCategoryMaster->editItemCategory($mreqs);
            return responseMsg(true, "Item Category Details Updated", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }
}
