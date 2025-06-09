<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\returnSelf;

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

    // Getting Photo url
    public function getPhotoUrlAttribute()
    {
        return $this->photo ? url('storage/' . $this->photo) : null;
    }

    //
    public function getShiftNameAttribute()
    {
        return config('constants.SHIFT_TYPES')[$this->shift_id] ?? 'Unknown Shift';
    }

    /**
     * | Add Member Details
     */
    public function addMember($req)
    {
        $mMember = new Member();
        $createdMember = $mMember->create($req);
        return $createdMember;
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
    public function fetchMember($name = null, $dueStatus = null)
    {
        $today = Carbon::now()->toDateString();
        $query = Member::select(
            '*',
            DB::raw("CONCAT('(storage/', photo) AS photo_url"),
            DB::raw("IF(membership_end < '$today', 1, 0) as due_status"),
            DB::raw("IF(membership_end < '$today', CEIL(DATEDIFF('$today', membership_end) / 30), 0) as months_due")

        )
            ->where('status', 1)
            ->orderBy('membership_end');

        if (!empty($name)) {
            $query->where('name', 'like', '%' . $name . '%')
                ->orwhere('phone', 'like', '%' . $name . '%');
        }

        if (!empty($dueStatus)) {
            $query->where('due_status', $dueStatus);
        }

        return $query;
    }
}
