<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanMaster extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * | Add Plan Details
     */
    public function addPlan($req)
    {
        $mPlanMaster = new PlanMaster();
        $mPlanMaster->create($req);
    }

    /**
     * | Edit Plan Details
     */
    public function editPlan($req)
    {
        $mPlanMaster = PlanMaster::findorfail($req['id']);
        $mPlanMaster->update($req);
    }

    /**
     * | Fetch Plan List
     */
    public function fetchPlan()
    {
       return PlanMaster::where('status',1)
                        ->orderBy('duration')
                        ->get();
    }
}
