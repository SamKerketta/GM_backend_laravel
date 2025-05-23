<?php

namespace App\Http\Controllers;

use App\IdGenerator;
use App\Models\Member;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * | Offline Payment
     */
    public function offlinePayment(Request $request)
    {
        try {
            $request->validate([
                'memberId'  => 'required',
                "forMonth"  => 'required'
            ]);
            $idGenerator  = new IdGenerator;
            $mTransaction = new Transaction();
            $mMember      = new Member();

            $monthTill = Carbon::parse($request->monthFrom)->addMonth($request->forMonth);

            $invoiceNo   = $idGenerator->generateInvoiceNo();
            $mReqs = [
                "member_id"      => $request->memberId,
                "amount_paid"    => $request->amountPaid,
                "payment_for"    => $request->paymentFor,
                "payment_date"   => $request->paymentDate,
                "payment_method" => $request->paymentMethod,
                "month_from"     => $request->monthFrom,
                "month_till"     => $monthTill,
                "invoice_no"     => $invoiceNo
            ];

            DB::beginTransaction();
            $mTransaction->store($mReqs);
            $mMember->where('id', $request->memberId)->update(['membership_end' => $monthTill]);
            DB::commit();

            return responseMsg(true, "Payment successful. Your Invoice no is " . $invoiceNo, $invoiceNo);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Send Whatsapp Message
     */
    public function sendWhatsapp(Request $request)
    {
        try {
            $request->validate([
                'memberId' => 'required|numeric'
            ]);
            $refMember = Member::find($request->memberId);

            if (!$refMember)
                throw new Exception("Requested user does not exists.");

            #_Whatsaap Message
            if (strlen($refMember->phone) == 10) {
              return  $whatsapp = (Whatsapp_Send(
                    $refMember->phone,
                    'membership_reminder',
                    // $request->template_id,
                    [
                        "content_type" => "text",
                        [
                            $refMember->name,
                            // "Tannu Fitness Center",
                            "Gears of Fead",
                            "May-2025",
                            "12-05-2025"
                        ]
                    ]
                ));
            }

            return responseMsg(true, "Message sent succesfully.", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }
}
