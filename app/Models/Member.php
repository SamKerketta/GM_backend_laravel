<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;
    protected $guarded = [];


    // Capitalize Name (e.g. TANNU SHARMA)
    public function getNameAttribute($value)
    {
        return strtoupper($value);
    }

    // Capitalize gender (e.g. Male, Female, Other)
    public function getGenderAttribute($value)
    {
        return ucfirst($value); // or strtoupper($value) if preferred
    }

    // Format membership_start as d-m-Y
    public function getMembershipStartAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

    // Format membership_end as d-m-Y
    public function getMembershipEndAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d-m-Y') : null;
    }

    /**
     * | Add Member Details
     */
    public function addMember($req)
    {
        $mMember = new Member();
        $mMember->create($req);
    }

    /**
     * | Edit Member Details
     */
    public function editMember($req)
    {
        $mMember = Member::findorfail($req['id']);
        $mMember->update($req);
    }

    /**
     * | Fetch Member List
     */
    public function fetchMember()
    {
        return Member::where('status', 1)
            ->orderBy('name');
    }
}
