<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;
    protected $guarded = [];

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
       return Member::where('status',1)
                        ->orderBy('name')
                        ->get();
    }
}
