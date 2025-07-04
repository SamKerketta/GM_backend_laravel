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

    private $_mMember;
    private $_idGenerator;
    private $_mTransaction;
    private $_invoiceNo;
    private $_reqs;
    private $_memberDetails;

    public function __construct()
    {
        $this->_mMember      = new Member();
        $this->_idGenerator  = new IdGenerator;
        $this->_mTransaction = new Transaction();
    }


    public function main($request)
    {
        $this->_reqs = $request->all();
        $this->readVariables();
        $this->calculatePayment();
        $this->storePayment();
    }

    public function readVariables()
    {
        $this->_invoiceNo     = $this->_mTransaction->generateInvoiceNo();
        $this->_memberDetails = $this->_mMember::find($this->_reqs->memberId);
    }

    public function calculatePayment()
    {
        try {
            // Process payment based on request type
            if ($this->_reqs->isArrear == true) {
                $mReqs = $this->processArrearPayment();
            } else
                return $this->processFullOrPartialPayment();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Payment calculation error: " . $e->getMessage());
            return responseMsg(false, "Payment calculation failed: " . $e->getMessage(), "");
        }
    }

    /**
     * Process arrear payment
     */
    private function processArrearPayment()
    {
        $dueBalance = $this->_memberDetails->due_balance;
        $mReqs = [
            "member_id"       => $this->_memberDetails->id,
            "amount_paid"     => $dueBalance,
            "arrear_amount"   => $dueBalance,
            "payment_for"     => $this->_reqs->paymentFor,
            "payment_method"  => $this->_reqs->paymentMethod,
            "invoice_no"      => $this->_invoiceNo,
        ];
    }


    /**
     * | Process full or partial payment
     */
    private function processFullOrPartialPayment()
    {
        // Logic for processing full or partial payment
    }

    private function storePayment()
    {
        //  Store the payment details in the database
    }
}
