<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function monthlyPayment(Request $request)
    {
        $todayDate = Carbon::now();
        $month     = $todayDate->format('m');
        $year      = $todayDate->format('Y');
        try {
            $data = Member::select(
                'name',
                'gender',
                'membership_start',
                'membership_end',
                'amount_paid',
                'payment_for',
                'payment_date',
                'month_from',
                'month_till',
                'invoice_no',
                // 'plan_name',
                // 'duration',
            )
                // ->join('plan_masters', 'plan_masters.id', 'members.plan_id')
                ->leftjoin('transactions', 'transactions.member_id', 'members.id')
                ->whereMonth('transactions.payment_date', $month)
                ->whereYear('transactions.payment_date', $year)
                ->get();
            return responseMsg(true, "Monthly Payment Report", $data);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | 
     */
    public function paymentReport(Request $request)
    {
        try {
            $request->validate([
                'startDate' => 'required|date',
                'endDate'   => 'required|date|after_or_equal:startDate',
            ]);

            $payments = Transaction::select(
                'name',
                'gender',
                // 'membership_start',
                // 'membership_end',
                'amount_paid',
                'payment_for',
                'payment_date',
                'month_from',
                'month_till',
                'invoice_no',
            )
                ->leftjoin('members', 'members.id', 'transactions.member_id')
                ->whereBetween('payment_date', [$request->startDate, $request->endDate])
                ->orderBy('payment_date', 'asc')
                ->get();

            return response()->json([
                'status'  => true,
                'message' => 'Payment report generated successfully.',
                'data'    => $payments
            ]);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Fetch Expiring Members
     */
    public function expiringPlans()
    {
        $today      = Carbon::today()->toDateString();
        $futureDate = Carbon::today()->addDays(15)->toDateString();

        return Member::select(
            '*',
            DB::raw("DATEDIFF(membership_end, '$today') as days_left")
        )
            ->where('status', 1)
            ->whereDate('membership_end', '>=', $today)
            ->whereDate('membership_end', '<=', $futureDate)
            ->orderBy('membership_end', 'asc')
            ->get();
    }
}
