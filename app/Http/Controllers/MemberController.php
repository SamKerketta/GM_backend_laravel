<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberRequest;
use App\Models\AttendanceLog;
use App\Models\Member;
use Exception;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    /**
     * ================== CRUD OF MEMBERS ======================
     */

    /**
     * | Add Members Data in Member table
     */
    public function createMember(StoreMemberRequest $request)
    {
        try {
            $mreqs = $this->makeMemberRequest($request);
            $mMember = new Member();
            $mMember->addMember($mreqs);
            return responseMsg(true, "Member has been added succesfully", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Fetch all Member List
     */
    public function memberList(Request $request)
    {
        try {
            $mMember    = new Member();
            $memberList = $mMember->fetchMember();

            return responseMsg(true, "List of Members", $memberList);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Deletion of the Member
     */
    public function deleteMember(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required'
            ]);
            $mMember    = new Member();
            $mMember->where('id', $request->id)->update(['status' => '0']);
            return responseMsg(true, "Member Deleted Succesfully", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Update Member Details
     */
    public function updateMember(StoreMemberRequest $request)
    {
        try {
            $request->validate(['id' => 'required']);
            $mreqs       = $this->makeMemberRequest($request);
            $mreqs       = array_merge($mreqs, ['id' => $request->id]);

            $mMember    = new Member();
            $mMember->editMember($mreqs);
            return responseMsg(true, "Member Details Updated", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Make Member Request Format
     */
    public function makeMemberRequest($request)
    {
        return [
            "name"              => $request->name,
            "dob"               => $request->dob,
            "gender"            => $request->gender,
            "email"             => $request->email,
            "phone"             => $request->phone,
            "address"           => $request->address,
            "membership_start"  => $request->membershipStart,
            "membership_end"    => $request->membershipEnd,
            "plan_id"           => $request->planId,
            "assigned_trainer"  => $request->assignedTrainer, // optional
            "photo"             => $request->photo,
            "id_proof"          => $request->idProof,
            "status"            => $request->status ?? 1
        ];
    }

    /**
     * | Biometric Logs
     */
    public function storeBiometric(Request $request)
    {
        try {
            $logData = $request->all();

            // Example: match user_id to member
            $member = Member::where('id', $logData['user_id'])->first();

            if ($member) {
                AttendanceLog::create([
                    'member_id'    => $member->id,
                    'checkin_time' => $logData['timestamp'],
                    'method'       => $logData['method'] ?? 'Unknown'
                ]);
            }
            return responseMsg(true, "Biomertic data stored", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }
}
