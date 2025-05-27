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
                'startDate' => 'nullable|date',
                'endDate'   => 'nullable|date|after_or_equal:startDate',
            ]);

            $startDate = $request->startDate ?? Carbon::now();
            $endDate   = $request->endDate ?? Carbon::now();

            $payments = Transaction::select(
                'transactions.id as transaction_id',
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
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->orderBy('payment_date', 'desc')
                ->get();

            $totalAmount                    =  $payments->sum('amount_paid');
            $paymentDetail['data']          = $payments; 
            $paymentDetail['total_amount '] = $totalAmount; 

            return response()->json([
                'status'  => true,
                'message' => 'Payment report generated successfully.',
                'data'    => $paymentDetail
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

    public function fetchMemberDues()
    {
        try {
            $today = Carbon::today()->toDateString();

            $membersWithDues = DB::table('members')
                ->select(
                    'members.id',
                    'members.name',
                    'members.membership_end',
                    DB::raw("SUM(plan_masters.price) as total_plan_amount"),
                    DB::raw("IFNULL(SUM(transactions.amount_paid), 0) as total_paid"),
                    DB::raw("(SUM(plan_masters.price) - IFNULL(SUM(transactions.amount_paid), 0)) as total_due"),
                    DB::raw("IF(SUM(transactions.amount_paid) < SUM(plan_masters.price), 'Dues', 'No Dues') as due_status")
                )
                ->join('plan_masters', 'plan_masters.id', '=', 'members.plan_id')
                ->leftJoin('transactions', function ($join) {
                    $join->on('transactions.member_id', '=', 'members.id');
                })
                ->where('members.status', 1)
                ->groupBy('members.id', 'members.name', 'members.membership_end')
                ->havingRaw('total_due > 0')
                ->orderBy('members.name')
                ->get();

            return responseMsg(true, "Memeber with dues List", $membersWithDues);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }
}
