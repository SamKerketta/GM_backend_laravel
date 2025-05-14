<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function monthlyPayment(Request $request)
    {
        $todayDate = Carbon::now();
        $month = $todayDate->format('m');
        $year  = $todayDate->format('Y');
        try {
            $data = Member::select('*')
                ->join('plan_masters', 'plan_masters.id', 'members.plan_id')
                ->leftjoin('transactions', 'transactions.member_id', 'members.id')
                ->whereMonth('transactions.transaction_date', $month)
                ->whereYear('transactions.transaction_date', $year)
                ->get();
            return responseMsg(true, "Monthly Payment Report", $data);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }
}
