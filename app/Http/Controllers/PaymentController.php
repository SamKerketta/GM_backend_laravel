<?php

namespace App\Http\Controllers;

use App\IdGenerator;
use App\Models\Member;
use App\Models\PlanMaster;
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
                "planId"    => 'required'
            ]);
            $idGenerator  = new IdGenerator;
            $mTransaction = new Transaction();
            $mMember      = new Member();
            $planDtls     = PlanMaster::find($request->planId);


            $monthTill = Carbon::parse($request->monthFrom)->addMonth($planDtls->duration);

            $invoiceNo = $idGenerator->generateInvoiceNo();
            $mReqs = [
                "member_id"      => $request->memberId,
                "amount_paid"    => $request->amountPaid,
                "payment_for"    => $request->paymentFor,
                // "payment_date"   => $request->paymentDate,
                "payment_method" => $request->paymentMethod,
                "month_from"     => $request->monthFrom,
                "month_till"     => $monthTill,
                "invoice_no"     => $invoiceNo
            ];

            DB::beginTransaction();
            $mTransaction->store($mReqs);
            $mMember->where('id', $request->memberId)->update(['membership_end' => $monthTill]);
            DB::commit();

            #_Request for whatsaap notification on success.
            $paymentNotificationReqs = new Request([
                "memberId"    => $request->memberId,
                "amountPaid"  => $request->amountPaid,
                "paymentDate" => $request->paymentDate,
                "monthFrom"   => $request->monthFrom,
                "monthTill"   => $monthTill,
            ]);
            $this->sendWhatsAppPaymentSuccessNotification($paymentNotificationReqs);

            return responseMsg(true, "Payment successful. Your Invoice no is " . $invoiceNo, $invoiceNo);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Payment Receipt
     */
    public function paymentReceipt(Request $request)
    {
        try {
            $request->validate([
                'transactionId'  => 'required',
            ]);
            $tranDetails = Transaction::select(
                // 'members.*',
                // 'transactions.*',
                'transactions.id as transaction_id',
                'name',
                'phone',
                'gender',
                DB::raw("DATE_FORMAT(membership_end, '%d-%m-%Y') as membership_end"),
                'invoice_no',
                'month_from',
                'month_till',
                'amount_paid',
                'payment_method',
                'payment_date',
                DB::raw("CONCAT(DATE_FORMAT(payment_date, '%h:%i %p')) as payment_time"),
                'plan_name',
                DB::raw("CONCAT(plan_masters.duration, ' ', IF(plan_masters.duration = 1, 'Month', 'Months')) as duration")
                // DB::raw("CONCAT(duration, ' month') as duration")
            )
                ->join('members', 'members.id', 'transactions.member_id')
                ->join('plan_masters', 'plan_masters.id', 'members.plan_id')
                ->where('transactions.id', $request->transactionId)
                ->first();

            return responseMsg(true, "Payment Receipt ",  $tranDetails);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsg(false, $e->getMessage(), "");
        }
    }


    /**
     * | Send Reminder Whatsapp Message
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
                $whatsapp = (Whatsapp_Send(
                    $refMember->phone,
                    'membership_reminder',
                    // $request->template_id,
                    [
                        "content_type" => "text",
                        [
                            $refMember->name,
                            // "Tannu Fitness Center",
                            "Gears of Fead",
                            "$500",
                            "May-2025",
                        ]
                    ]
                ));
            }

            return responseMsg(true, "Message sent succesfully.", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Send Notification on Payment Success
     */
    public function sendWhatsAppPaymentSuccessNotification($request)
    {
        $monthFrom = Carbon::parse($request->monthFrom)->format('M');
        $monthTill = $request->monthTill->format('M');
        try {
            $refMember = Member::find($request->memberId);

            if (!$refMember)
                throw new Exception("Requested user does not exists.");

            #_Whatsaap Message
            if (strlen($refMember->phone) == 10) {
                $whatsapp = (Whatsapp_Send(
                    $refMember->phone,
                    'payment_success_notification',
                    [
                        "content_type" => "text",
                        [
                            $refMember->name,
                            '₹' . $request->amountPaid,
                            "$monthFrom to $monthTill",              # Payment for month
                            $request->paymentDate,                   # Transaction Date
                            // "Tannu Fitness Center",
                            "Gears of Fead",
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
