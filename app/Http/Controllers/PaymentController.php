<?php

namespace App\Http\Controllers;

use App\IdGenerator;
use App\Models\Member;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * | Offline Payment
     */
    public function offlinePayment(Request $request)
    {
        try {
            $request->validate([
                'memberId' => 'required'
            ]);
            $idGenerator  = new IdGenerator;
            $mTransaction = new Transaction();

            $invoiceNo   = $idGenerator->generateInvoiceNo();
            $mReqs = [
                "member_id"      => $request->memberId,
                "amount_paid"    => $request->amountPaid,
                "payment_for"    => $request->paymentFor,
                "payment_date"   => $request->paymentDate,
                "payment_method" => $request->paymentMethod,
                "invoice_no"     => $invoiceNo
            ];
            $mTransaction->store($mReqs);
            return responseMsg(true, "Payment successful. Your Invoice no is " . $invoiceNo, $invoiceNo);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Send Whatsaap Message
     */
    public function sendWhatsaap(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|numeric'
            ]);
            $refMember = Member::find($request->id);

            #_Whatsaap Message
            if (strlen($request->mobile_no) == 10) {
                $whatsapp = (Whatsapp_Send(
                    $request->mobile_no,
                    $request->template_id,
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
            return $whatsapp;
            return responseMsg(true, "", "");

        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }
}
