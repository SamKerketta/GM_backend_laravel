<?php

namespace App;

use App\Models\Member;
use App\Models\Transaction;
use Carbon\Carbon;

class IdGenerator
{

    /**
     * | For generating Invoice No
     */
    public function generateInvoiceNo()
    {
        $todayDate    = Carbon::now();
        $year         = $todayDate->format('Y');
        $month        = $todayDate->format('m');

        // Get latest invoice for this year and month
        $lastInvoice = Transaction::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->latest('id')
            ->first();

        if ($lastInvoice) {
            // Extract the last 4 digits (counter)
            $lastCounter = (int) substr($lastInvoice->invoice_no, -4);
            $counter = $lastCounter + 1;
        } else {
            $counter = 1; // Reset counter if it's a new month
        }

        $counterStr = str_pad($counter, 4, '0', STR_PAD_LEFT);

        $invoiceNo = 'INV' . $year . $month . $counterStr;
        return $invoiceNo;
    }

    /**
     * | For generating MemberId
     */
    public function generateMemberId()
    {
        $todayDate    = Carbon::now();
        $year         = $todayDate->format('Y');
        $month        = $todayDate->format('m');

        // Get latest invoice for this year and month
        $lastMember = Member::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->latest('id')
            ->first();

        if ($lastMember) {
            // Extract the last 4 digits (counter)
            $lastCounter = (int) substr($lastMember->member_id, -4);
            $counter = $lastCounter + 1;
        } else {
            $counter = 1; // Reset counter if it's a new month
        }

        $counterStr = str_pad($counter, 4, '0', STR_PAD_LEFT);

        $invoiceNo = 'TFC' . $year . $month . $counterStr;
        return $invoiceNo;
    }
}
