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

            $startDate = Carbon::parse($request->startDate)->startOfDay() ?? Carbon::now()->startOfDay();
            $endDate   = Carbon::parse($request->endDate)->endOfDay() ?? Carbon::now()->endOfDay();

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
                ->orderBy('payment_date', 'desc');
            // ->get();

            $payments     = paginator($payments, $request);
            $totalAmount  = collect($payments['data'])->sum('amount_paid');

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
                    DB::raw("'Dues'as due_status")
                )
                ->join('plan_masters', 'plan_masters.id', '=', 'members.plan_id')
                ->leftJoin('transactions', function ($join) {
                    $join->on('transactions.member_id', '=', 'members.id');
                })
                ->where('members.status', 1)
                ->havingRaw("total_due > 0")
                ->groupBy('members.id', 'members.name', 'members.membership_end')
                ->orderByDesc('total_due')
                ->get();

            return responseMsg(true, "Members with dues", $membersWithDues);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Dashboard Reporting
     * | Today’s Collection
     * | Monthly Demand Target
     * | Demand Till Date
     * | Demand Remaining
     * | Total Amount Received (This Month)
     * | Total Members Paid
     * | Unpaid Amount (This Month)
     * | Members with Dues
     */
    public function dasboardReport(Request $request)
    {
        $today        = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth   = Carbon::now()->endOfMonth();

        // 1. Today’s Collection
        $todaysCollection = Transaction::whereDate('payment_date', $today)->sum('amount_paid');

        // 2. Monthly Demand Target
        $monthlyDemand = Member::select(DB::raw('SUM(plan_masters.price / (plan_masters.duration / 30)) as monthly_total'))
            ->join('plan_masters', 'members.plan_id', '=', 'plan_masters.id')
            ->where('members.status', 1)
            ->value('monthly_total');

        // 3. Demand Till Date (Linear accrual)
        $daysPassed = $today->day;
        $totalDays  = $today->daysInMonth;
        $demandTillDate = round(($monthlyDemand / $totalDays) * $daysPassed);

        // 4. Amount Paid This Month
        $amountThisMonth = Transaction::whereBetween('payment_date', [$startOfMonth, $today])->sum('amount_paid');

        // 5. Demand Remaining
        $demandRemaining = $demandTillDate - $amountThisMonth;

        // 6. Total Members Paid
        $membersPaid = Transaction::whereBetween('payment_date', [$startOfMonth, $today])
            ->distinct('member_id')
            ->count('member_id');

        // 7. Unpaid Amount (This Month)
        // Assuming full monthly fee not paid = due
        $today = $today->format('Y-m-d');
        $unpaidAmount = Member::join('plan_masters', 'members.plan_id', '=', 'plan_masters.id')
            ->select(
                DB::raw("IF(DATE(members.membership_end) < '$today', 1, 0) as due_status"),
                DB::raw('SUM(plan_masters.price / (plan_masters.duration / 30)) as unpaid_total')
            )
            ->groupBy(DB::raw("due_status"))
            ->havingRaw('due_status = 1')
            ->value('unpaid_total');

        // 8. Members With Dues
        $membersWithDues = Member::select(DB::raw("IF(DATE(members.membership_end) < '$today', 1, 0) as due_status"))
            ->havingRaw('due_status = 1')
            ->count();

        // 9. Total Demand: sum of all members' monthly fee (or actual plan price per month)
        $totalDemand = Member::where('members.status', 1)
            ->join('plan_masters', 'members.plan_id', '=', 'plan_masters.id')
            ->select(DB::raw('SUM(plan_masters.price / (plan_masters.duration / 30)) as total'))
            ->value('total') ?? 0;


        // 10. Total Collection: total amount collected so far (all time)
        $totalCollection = Transaction::sum('amount_paid');

        // 11. Balance Due
        $balanceDue = $totalDemand - $totalCollection;

        // 12. Collection %
        $collectionPercentage = $totalDemand > 0 ? ($totalCollection / $totalDemand) * 100 : 0;

        $data = [
            'today_collection'                 => round($todaysCollection, 2),
            'monthly_demand_target'            => round($monthlyDemand, 2),
            'demand_till_date'                 => round($demandTillDate, 2),
            'demand_remaining'                 => round($demandRemaining, 2),
            'total_amount_received_this_month' => round($amountThisMonth, 2),
            'total_members_paid'               => $membersPaid,
            'unpaid_amount'                    => round($unpaidAmount, 2),
            'members_with_dues'                => $membersWithDues,
            'total_demand'                     => round($totalDemand, 2),
            'total_collection'                 => round($totalCollection, 2),
            'balance_due'                      => round($balanceDue, 2),
            'collection_percentage'            => round($collectionPercentage, 2),
        ];

        return responseMsg(true, "Dashboard Data", $data);
    }
}
