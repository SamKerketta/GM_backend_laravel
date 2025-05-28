<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberRequest;
use App\IdGenerator;
use App\Models\AttendanceLog;
use App\Models\Member;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $paymentController  = new PaymentController;
            $idGenerator        = new IdGenerator;
            $mMember            = new Member();
            $memberId      = $idGenerator->generateMemberId();
            $mreqs         = $this->makeMemberRequest($request);
            $mreqs         = array_merge($mreqs, ['member_id' => $memberId]);

            $memberDetails = $mMember->addMember($mreqs);
            $msg           = "Member has been added succesfully and member id is  $memberId";

            if ($request->isPayment == true) {
                $paymentReqs =  new Request([
                    'memberId'      => $memberDetails->id,
                    'forMonth'      => $request->forMonth,
                    "amountPaid"    => $request->amountPaid,
                    "paymentFor"    => $request->paymentFor,
                    "paymentDate"   => $request->paymentDate,
                    "paymentMethod" => $request->paymentMethod,
                    "monthFrom"     => $request->monthFrom,
                ]);
                $paymentDetails = $paymentController->offlinePayment($paymentReqs);
                $invoiceNo = $paymentDetails->original['data'];
                $msg = "Member has been added succesfully. Member id is $memberId & Invoice No is $invoiceNo";
            }

            return responseMsg(true, $msg, "");
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

            $perPage    = $request->perPage ?? 10;
            $name       = $request->name;
            $phone     = $request->phone;
            $dueStatus  = $request->dueStatus;

            $mMember    = new Member();
            $memberList = $mMember->fetchMember($name, $phone, $dueStatus)
                ->paginate($perPage);

            // remove null fields in each item
            $memberList->getCollection()->transform(function ($item) {
                return collect($item)->map(function ($value) {
                    return is_null($value) ? '' : $value;
                })->all();
            });

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
            $mMember    = new Member();
            $mreqs      = $this->makeMemberRequest($request);
            $mreqs      = array_merge($mreqs, ['id' => $request->id]);

            $mMember->editMember($mreqs);
            return responseMsg(true, "Member Details Updated", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Get Member Details By Id
     */
    public function getMemberDetail(Request $request)
    {
        try {
            $request->validate(['id' => 'required']);
            $memberDetails = Member::find($request->id);

            return responseMsg(true, "Member Details", $memberDetails);
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
