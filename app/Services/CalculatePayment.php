<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Transaction;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\IdGenerator;

class CalculatePayment
{
    public function calculatePayment($request)
    {
        $mMember = new Member();
        $idGenerator = new IdGenerator;

        $invoiceNo = $idGenerator->generateInvoiceNo();

        try {

            // Process payment based on request type
            switch (true) {
                case $request->isArrear == true:
                    return $this->processArrearPayment($request, $mMember);

                case $request->isPartialPayment == true:
                    return $this->processPartialPayment($request, $mMember);

                default:
                    return $this->processFullOrPartialPayment($request, $mMember);
            }

            DB::beginTransaction();
            $mTransaction->store($mReqs);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Payment calculation error: " . $e->getMessage());
            return responseMsg(false, "Payment calculation failed: " . $e->getMessage(), "");
        }
    }

    /**
     * Process arrear payment
     */
    private function processArrearPayment(Request $request, Member $mMember)
    {
        $member = $mMember::find($request->memberId);
        if (!$member)
            throw new Exception("Member does not exists.");

        $mReqs = [
            "member_id"       => $request->memberId,
            "amount_paid"     => $member->arrear_amount,
            "payment_for"     => $request->paymentFor,
            "payment_method"  => $request->paymentMethod,
            "invoice_no"      => $invoiceNo,
        ];
    }

    private function processPartialPayment(Request $request, Member $mMember)
    {
        // Logic for processing partial payment
    }

    private function processFullOrPartialPayment(Request $request, Member $mMember)
    {
        // Logic for processing full payment
    }
}
