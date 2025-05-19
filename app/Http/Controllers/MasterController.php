<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\StorePlanRequest;
use App\Models\Inventory;
use App\Models\ItemCategoryMaster;
use App\Models\PlanMaster;
use App\Models\VendorMaster;
use Exception;
use Illuminate\Http\JsonResponse;
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
            $mreqs = [
                "vendor_name"    => $request->vendorName,
                "vendor_address" => $request->vendorAddress
            ];
            $mVendorMaster = new VendorMaster();
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
            $mreqs =  [
                'id'             => $request->id,
                'vendor_name'    => $request->vendorName,
                'vendor_address' => $request->vendorAddress,
                'status'         => $request->status
            ];
            $mVendorMaster = new VendorMaster();
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
            $mreqs = [
                "category_name"    => $request->categoryName,
            ];
            $mItemCategoryMaster = new ItemCategoryMaster();
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
            $mreqs =  [
                'id'             => $request->id,
                'category_name'  => $request->categoryName,
                'status'         => $request->status ];

            $mItemCategoryMaster = new ItemCategoryMaster();
            $mItemCategoryMaster->editItemCategory($mreqs);
            return responseMsg(true, "Item Category Details Updated", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * ================== CRUD OF INVENTORY ITEM ======================
     */
    
    /**
     * | Add Data of Item in Inventory Master
     */
    public function createItem(StoreItemRequest $request)
    {
        try {
            $mreqs = $this->makeItemRequest($request);
            $mInventory = new Inventory();
            $mInventory->addItem($mreqs);    
            return responseMsg(true, "Item Added Succesfully", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Fetch all Inventory Item
     */
    public function itemList(Request $request)
    {
        try {
            $mInventory = new Inventory();
            $itemList  = $mInventory->fetchItem();

            return responseMsg(true, "List of Inventory Item ", $itemList);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Deletion of the Item category
     */
    public function deleteItem(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required'
            ]);
            $mInventory = new Inventory();
            $mInventory->where('id', $request->id)->update(['status' => '0']);
            return responseMsg(true, "Inventory Item Deleted Succesfully", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Update Inventory Item Details
     */
    public function updateItem(StoreItemRequest $request)
    {
        try {
            $request->validate(['id' => 'required']);
            $mreqs      = $this->makeItemRequest($request);
            $mreqs      = array_merge($mreqs,['id'=>$request->id]);
            $mInventory = new Inventory();
            $mInventory->editItem($mreqs);
            return responseMsg(true, "Inventory Item Details Updated", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Make request format
     */
    public function makeItemRequest($request)
    {
        return [
            "vendor_id"            => $request->vendorId,
            "item_category_id"     => $request->itemCategoryId,
            "item_name"            => $request->itemName,
            "brand"                => $request->brand,
            "quantity"             => $request->quantity,
            "unit_cost"            => $request->unitCost,
            "total_cost"           => $request->totalCost,
            "date_of_purchase"     => $request->dateOfPurchase,
            "warranty_expiry_date" => $request->warrantyExpiryDate,
            "status"               => $request->status ?? 1,
            "description"          => $request->description,
            "notes"                => $request->notes,
            "image"                => $request->image
        ];
    }

     /**
     * ================== CRUD OF PLANS ======================
     */
    
    /**
     * | Add Plans Data in Plans Master
     */
    public function createPlan(StorePlanRequest $request)
    {
        try {
            $mreqs = $this->makePlanRequest($request);
            $mPlanMaster = new PlanMaster();
            $mPlanMaster->addPlan($mreqs);    
            return responseMsg(true, "Plan Added Succesfully", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Fetch all Plan List
     */
    public function planList(Request $request)
    {
        try {
            $mPlanMaster = new PlanMaster();
            $planList    = $mPlanMaster->fetchPlan();


            return responseMsg(true, "List of Plans", remove_null($planList));
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Deletion of the Plan
     */
    public function deletePlan(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required'
            ]);
            $mPlanMaster = new PlanMaster();
            $mPlanMaster->where('id', $request->id)->update(['status' => '0']);
            return responseMsg(true, "Plan Deleted Succesfully", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Update Plan Item Details
     */
    public function updatePlan(StorePlanRequest $request)
    {
        try {
            $request->validate(['id' => 'required']);
            $mreqs       = $this->makePlanRequest($request);
            $mreqs       = array_merge($mreqs,['id'=>$request->id]);
            
            $mPlanMaster = new PlanMaster();
            $mPlanMaster->editPlan($mreqs);
            return responseMsg(true, "Plan Details Updated", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Make Plan Request Format
     */
    public function makePlanRequest($request)
    {
        return [
            "plan_name"            => $request->planName,
            "duration"             => $request->duration,
            "price"                => $request->price,
            "discount_percentage"  => $request->discount_percentage,
            "description"          => $request->description,
            "status"               => $request->status ?? 1
        ];
    }
}
