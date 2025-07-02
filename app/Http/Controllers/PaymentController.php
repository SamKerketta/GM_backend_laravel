<?php

namespace App\Http\Controllers;

use App\IdGenerator;
use App\Models\Member;
use App\Models\PlanMaster;
use App\Models\Transaction;
use App\Models\WhatsappLog;
use App\Services\CalculatePayment;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class PaymentController extends Controller
{
    /**
     * | Offline Payment
     */
    public function offlinePayment(Request $request)
    {
        try {
            $request->validate([
                'memberId'      => 'required',
                "planId"        => 'nullable|numeric',
                "amountPaid"    => 'nullable|numeric',
                "paymentMethod" => 'required',
                "monthFrom"     => 'nullable|date|required_if:paymentFor,plan',
            ]);

            $calculatePayment  = new CalculatePayment;
            $todayDate         = Carbon::now()->format('d-m-Y');
            $member            = Member::find($request->memberId);

            if (!$member)
                throw new Exception("Invalid member");

            // Check if payment_date is before membership_end
            if (strtotime($request->monthFrom) < strtotime($member->membership_end) && $request->paymentFor == 'plan')
                throw new Exception("Payment already done till $member->membership_end");

            $paymentDetail = $this->calculatePayment($request);
            $invoiceNo = $paymentDetail['invoiceNo'];
            // $paymentDetail = $calculatePayment->calculatePayment($request);

            if (!$request->paymentFor == 'arrear') {
                #_Request for whatsaap notification on success.
                $paymentNotificationReqs = new Request([
                    "memberId"    => $request->memberId,
                    "amountPaid"  => $paymentDetail['amountPaid'],
                    "paymentDate" => $todayDate,
                    "monthFrom"   => $paymentDetail['monthFrom'],
                    "monthTill"   => $paymentDetail['monthTill'],
                ]);
                $this->sendWhatsAppPaymentSuccessNotification($paymentNotificationReqs);
            }

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
                'due_balance',
                'net_amount',
                'arrear_amount',
                'discount_amount',
                'payment_for',
                DB::raw("IF(payment_for = 'plan', 'Plan', 'Arrear') as payment_for_type"),
                'transactions.status',
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
     * | Whatsapp Payment Reminder
     */
    public function paymentReminder(Request $request)
    {
        try {
            $request->validate([
                'memberId' => 'required|numeric'
            ]);
            $refMember = Member::find($request->memberId);

            if (!$refMember)
                throw new Exception("Requested user does not exists.");

            $forMonth = $refMember->membership_end ? Carbon::parse($refMember->membership_end)->format('M-Y') : Carbon::now()->format('M-Y');

            $dueDetail = $this->calculateDue($refMember);
            if (!$dueDetail)
                throw new Exception("No dues found for the member.");

            #_Whatsaap Message
            if (strlen($refMember->phone) == 10) {
                $whatsapp = (Whatsapp_Send(
                    $refMember->phone,
                    'payment_reminder',
                    // $request->template_id,
                    [

                        "name" => $refMember->name,
                        // "gym_name"  => "Tannu Fitness Center",
                        "gym_name"  => "Gears of Fead",
                        "total_due" => '₹' .  $dueDetail->total_due,
                        "for_month" => $forMonth,
                    ]
                ));

                // return $whatsapp;

                $whatsappReqs = new Request([
                    "memberId"   => $request->memberId,
                    "phone"      => $refMember->phone,
                    "templateId" => 'payment_reminder',
                    "status"     => $whatsapp['status'],
                    "response"   => $whatsapp['response'],
                ]);
                $this->whatsappLogs($whatsappReqs);

                # Updating is_notified true
                if ($whatsapp['status'] == 'success')
                    $refMember->where('id', $request->memberId)->update(['notified' => 1]);
            }

            return responseMsg(true, "Message sent succesfully.", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Calculate the total dues for a member
     * | This function is used to calculate the total dues for a member
     * | It takes the member ID as a parameter and returns the total dues
     */
    public function calculateDue($refMember)
    {
        $totalDueAmount = 0;
        $today          = Carbon::now()->toDateString();
        $dueDetail      = DB::table('members')
            ->select(
                'members.id',
                'name',
                'membership_end',
                'due_balance',
                'price',
                DB::raw("IF(membership_end < '$today', 1, 0) as due_status"),
                DB::raw("IF(due_balance > 0, 1, 0) as arrear_status"),
            )
            ->join('plan_masters', 'plan_masters.id', '=', 'members.plan_id')
            ->where('members.status', 1)
            ->where('members.id', $refMember->id)
            ->first();

        if ($dueDetail->due_status == 0 && $dueDetail->arrear_status == 0)
            throw new Exception("No dues found for the member.");

        if ($dueDetail->due_status == 1 && $dueDetail->arrear_status == 1) {
            $totalDueAmount = $dueDetail->due_balance + $dueDetail->price;
        }

        if ($dueDetail->arrear_status == 1 && $dueDetail->due_status == 0) {
            $totalDueAmount = $dueDetail->due_balance;
        }
        if ($dueDetail->due_status == 1 && $dueDetail->arrear_status == 0) {

            $dueMonths = Carbon::parse($dueDetail->membership_end)->diffInMonths($today);
            $totalDueAmount = $dueMonths * $dueDetail->price;
        }

        $dueDetail->total_due = $totalDueAmount;
        return $dueDetail;
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
                        "name"              => $refMember->name,
                        "amount_paid"       => '₹' . $request->amountPaid,
                        "payment_for_month" => "$monthFrom to $monthTill",              # Payment for month
                        "transaction_date"  => $request->paymentDate,                   # Transaction Date
                        "gym_name"          => "Gears of Fead",
                        // "gym_name" => "Tannu Fitness Center",
                    ]
                ));

                $whatsappReqs = new Request([
                    "memberId"   => $request->memberId,
                    "phone"      => $refMember->phone,
                    "templateId" => 'payment_success_notification',
                    "status"     => $whatsapp['status'],
                    "response"   => $whatsapp['response'],
                ]);
                $this->whatsappLogs($whatsappReqs);
            }

            return responseMsg(true, "Message sent succesfully.", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Calculate Payment
     */
    public function calculatePayment($request)
    {
        # Variable assignments
        $idGenerator  = new IdGenerator;
        $mTransaction = new Transaction();
        $mMember      = new Member();
        $mPlanMaster  = new PlanMaster();

        $member = $mMember::find($request->memberId);
        if (!$member)
            throw new Exception("Member does not exists.");

        $invoiceNo = $idGenerator->generateInvoiceNo();

        # Case 1 : | Only Arrear Payment

        if ($request->isArrear == true) {
            $amountPaid = $member->due_balance;
            $mReqs = [
                "member_id"       => $request->memberId,
                "amount_paid"     => $amountPaid,
                "arrear_amount"   => $amountPaid,
                "payment_for"     => $request->paymentFor,
                "payment_method"  => $request->paymentMethod,
                "invoice_no"      => $invoiceNo,
            ];

            DB::beginTransaction();
            $tranDtls = $mTransaction->store($mReqs);
            $mMember->where('id', $request->memberId)->update([
                'due_balance' => 0,
                'last_tran_id' => $tranDtls->id,
            ]);
            DB::commit();
        }


        # Case 2: | Full Payment And Partial Payment
        if ($request->isArrear == false || $request->isAdmission == true) {
            $admissionFee   = 0;

            $planDtls = $mPlanMaster::find($request->planId);
            if (!$planDtls)
                throw new Exception("Invalid plan selected.");

            if ($request->isAdmission == true)
                $admissionFee = $planDtls->admission_fee;

            $planAmount     = $planDtls->price;
            $arrearAmount   = $member->due_balance;
            $discountAmount = $request->discount ?? 0;
            $netAmount      = $planAmount + $admissionFee;

            // Calculate final amount after discount
            $finalAmount = $planAmount + $arrearAmount  + $admissionFee - $discountAmount;

            // Ensure final amount is not negative
            if ($finalAmount < 0)
                throw new Exception("Discount cannot exceed the amount paid.");

            // Calculate due amount based on whether it's a partial payment
            if ($request->isPartialPayment == true) {
                $amountPaid = $request->amountPaid;
                $dueAmount  = $finalAmount - $amountPaid;
            } else {
                $amountPaid = $finalAmount;
                $dueAmount  = 0;
            }
            // Ensure amount paid is not greater than final amount
            if ($amountPaid > $finalAmount)
                throw new Exception("Amount paid cannot exceed the final amount.");

            // Prepare data for transaction
            $mReqs = [
                "member_id"       => $request->memberId,
                "net_amount"      => $netAmount,
                "amount_paid"     => $amountPaid,
                "arrear_amount"   => $arrearAmount,
                "discount_amount" => $discountAmount,
                "payment_for"     => $request->paymentFor,
                "payment_method"  => $request->paymentMethod,
                "month_from"      => Carbon::parse($request->monthFrom)->format('Y-m-d'),
                "month_till"      => Carbon::parse($request->monthFrom)->addMonth($planDtls->duration)->format('Y-m-d'),
                "invoice_no"      => $invoiceNo,
            ];

            DB::beginTransaction();
            $tranDtls =  $mTransaction->store($mReqs);
            $mMember->where('id', $request->memberId)->update([
                'due_balance'    => $dueAmount,
                'membership_end' => $mReqs['month_till'],
                'plan_id'        => $request->planId,
                'last_tran_id'   => $tranDtls->id,
            ]);
            DB::commit();
        }

        return [
            'invoiceNo'    => $invoiceNo,
            'amountPaid'   => $amountPaid,
            'monthFrom'    => $mReqs['month_from'] ?? "",
            'monthTill'    => $mReqs['month_till'] ?? "",
        ];
    }

    /**
     * | Store Whatsapp Logs
     */
    public function whatsappLogs($request)
    {
        $mWhatsappLog = new WhatsappLog();
        $requestLog = [
            "member_id"    => $request->memberId,
            "phone"        => $request->phone,
            "template_id"  => $request->templateId,
            "status"       => $request->status,
            "response"     => $request->response,
            "sent_at"      => Carbon::now()
        ];
        $mWhatsappLog->createLog($requestLog);
    }
}
