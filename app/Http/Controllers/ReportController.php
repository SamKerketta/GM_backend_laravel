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

            $startDate = Carbon::parse($request->startDate)->startOfDay();
            $endDate   = Carbon::parse($request->endDate)->endOfDay();
            $name      = $request->name;

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
                ->orderBy('payment_date', 'desc')->active();


            // ✅ Apply date filter if provided
            if (!empty($request->startDate && $request->endDate)) {
                $payments->whereBetween('payment_date', [$startDate, $endDate]);
            }

            // ✅ Apply name filter if provided
            if (!empty($name)) {
                $payments->where('name', 'like', '%' . $name . '%');
            }

            $baseQuery = $payments;

            $payments     = paginator($payments, $request);
            $totalAmount  = $baseQuery->sum('amount_paid');

            $paymentDetail['data']         = $payments;
            $paymentDetail['total_amount'] = $totalAmount;

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
            $membersWithDues = $this->memberdueQuery()->get();

            return responseMsg(true, "Members with dues", $membersWithDues);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Query for membership due
     */
    public function memberdueQuery()
    {
        return DB::table('members')
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
            ->orderByDesc('total_due');
    }

    /**
     * | Second Version of Dashboard 
     */
    public function dasboardReport2(Request $request)
    {
        try {
            $today        = Carbon::today();
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth   = Carbon::now()->endOfMonth();

            # 1. Mpnthly Revenue 
            $monthlyRevenue = Transaction::select(
                DB::raw("YEAR(payment_date) as year"),
                DB::raw("MONTH(payment_date) as month"),
                DB::raw("SUM(amount_paid) as total")
            )
                ->where('payment_date', '>=', now()->subMonths(5)->startOfMonth()) // last 6 months
                ->groupBy(DB::raw("YEAR(payment_date), MONTH(payment_date)"))
                ->orderByRaw("YEAR(payment_date), MONTH(payment_date)")
                ->get();

            // Prepare last 6 months labels (month name) and total values
            $allMonths = collect(range(0, 5))->map(function ($i) {
                $date = now()->subMonths(5 - $i);
                return [
                    'key'   => $date->format('Y-m'),       // unique key to match results
                    'label' => $date->format('M'),         // short month name
                ];
            });

            // Build series from monthlyRevenue results
            $labels1 = [];
            $series1 = [];

            foreach ($allMonths as $month) {
                $match = $monthlyRevenue->first(function ($item) use ($month) {
                    return sprintf('%04d-%02d', $item->year, $item->month) === $month['key'];
                });

                $labels1[] = $month['label'];
                $series1[] = $match ? (float) $match->total : 0;
            }

            // --- Chart 2: DCB Report
            $totalCollection = Transaction::sum('amount_paid');
            $totalDemand = Member::where('members.status', 1)
                ->join('plan_masters', 'members.plan_id', '=', 'plan_masters.id')
                ->select(DB::raw('SUM(plan_masters.price / (plan_masters.duration / 30)) as total'))
                ->value('total') ?? 0;
            $balance = $totalDemand - $totalCollection;
            $arrear = Member::join('plan_masters', 'members.plan_id', '=', 'plan_masters.id')
                ->whereDate('membership_end', '<', now())
                ->select(DB::raw('SUM(plan_masters.price / (plan_masters.duration / 30)) as due'))
                ->value('due') ?? 0;

            // --- Chart 3: Members Joined Monthly (Last 6 Months)
            $monthlyMembers = Member::select(
                DB::raw("YEAR(created_at) as year"),
                DB::raw("MONTH(created_at) as month"),
                DB::raw("COUNT(*) as count")
            )
                ->where('created_at', '>=', now()->subMonths(5)->startOfMonth()) // last 6 months
                ->groupBy(DB::raw("YEAR(created_at), MONTH(created_at)"))
                ->orderByRaw("YEAR(created_at), MONTH(created_at)")
                ->get();

            // Build last 6 months (month-year) labels and member counts
            $allMonths = collect(range(0, 5))->map(function ($i) {
                $date = now()->subMonths(5 - $i);
                return [
                    'key'   => $date->format('Y-m'),
                    'label' => $date->format('M')
                ];
            });

            $labels3 = [];
            $series3 = [];

            foreach ($allMonths as $month) {
                $match = $monthlyMembers->first(function ($item) use ($month) {
                    return sprintf('%04d-%02d', $item->year, $item->month) === $month['key'];
                });

                $labels3[] = $month['label'];
                $series3[] = $match ? (int) $match->count : 0;
            }

            // --- Chart 4: Shift Wise Members
            $shiftWise = Member::select('shift_id', DB::raw("COUNT(*) as total"))
                ->groupBy('shift_id')
                ->get();
            $shiftTypes = config('constants.SHIFT_TYPES'); // E.g., [1 => 'Morning', 2 => 'Evening', 3 => 'Others']
            $labels4 = [];
            $series4 = [];

            foreach ($shiftTypes as $id => $name) {
                $labels4[] = $name;
                $series4[] = $shiftWise->firstWhere('shift_id', $id)->total ?? 0;
            }

            // --- Final Response
            return response()->json([
                'status' => true,
                'message' => 'Dashboard Data',
                'data' => [
                    [
                        'serial' => 1,
                        'name' => 'Monthly Revenue',
                        'type' => 'Area Chart',
                        'labels' => $labels1,
                        'series' => $series1
                    ],
                    [
                        'serial' => 2,
                        'name' => 'DCB Report',
                        'type' => 'Pie Chart',
                        'labels' => ['Collection', 'Balance', 'Arrear'],
                        'series' => [
                            round($totalCollection, 2),
                            round($balance, 2),
                            round($arrear, 2)
                        ]
                    ],
                    [
                        'serial' => 3,
                        'name' => 'Members',
                        'type' => 'Line Chart',
                        'labels' => $labels3,
                        'series' => $series3
                    ],
                    [
                        'serial' => 4,
                        'name' => 'Shift Wise Members',
                        'type' => 'Bar Chart',
                        'labels' => $labels4,
                        'series' => $series4
                    ]
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ]);
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
    // public function dasboardReport(Request $request)
    // {
    //     $today        = Carbon::today();
    //     $startOfMonth = Carbon::now()->startOfMonth();
    //     $endOfMonth   = Carbon::now()->endOfMonth();

    //     // 1. Today’s Collection
    //     $todaysCollection = Transaction::whereDate('payment_date', $today)->sum('amount_paid');

    //     // 2. Monthly Demand Target
    //     $monthlyDemand = Member::select(DB::raw('SUM(plan_masters.price / (plan_masters.duration / 30)) as monthly_total'))
    //         ->join('plan_masters', 'members.plan_id', '=', 'plan_masters.id')
    //         ->where('members.status', 1)
    //         ->value('monthly_total');

    //     // 3. Demand Till Date (Linear accrual)
    //     $daysPassed = $today->day;
    //     $totalDays  = $today->daysInMonth;
    //     $demandTillDate = round(($monthlyDemand / $totalDays) * $daysPassed);

    //     // 4. Amount Paid This Month
    //     $amountThisMonth = Transaction::whereBetween('payment_date', [$startOfMonth, $today])->sum('amount_paid');

    //     // 5. Demand Remaining
    //     $demandRemaining = $demandTillDate - $amountThisMonth;

    //     // 6. Total Members Paid
    //     $membersPaid = Transaction::whereBetween('payment_date', [$startOfMonth, $today])
    //         ->distinct('member_id')
    //         ->count('member_id');

    //     // 7. Unpaid Amount (This Month)
    //     // Assuming full monthly fee not paid = due
    //     $today = $today->format('Y-m-d');
    //     $unpaidAmount = Member::join('plan_masters', 'members.plan_id', '=', 'plan_masters.id')
    //         ->select(
    //             DB::raw("IF(DATE(members.membership_end) < '$today', 1, 0) as due_status"),
    //             DB::raw('SUM(plan_masters.price / (plan_masters.duration / 30)) as unpaid_total')
    //         )
    //         ->groupBy(DB::raw("due_status"))
    //         ->havingRaw('due_status = 1')
    //         ->value('unpaid_total');

    //     // 8. Members With Dues
    //     $membersWithDues = Member::select(DB::raw("IF(DATE(members.membership_end) < '$today', 1, 0) as due_status"))
    //         ->havingRaw('due_status = 1')
    //         ->count();

    //     // 9. Total Demand: sum of all members' monthly fee (or actual plan price per month)
    //     $totalDemand = Member::where('members.status', 1)
    //         ->join('plan_masters', 'members.plan_id', '=', 'plan_masters.id')
    //         ->select(DB::raw('SUM(plan_masters.price / (plan_masters.duration / 30)) as total'))
    //         ->value('total') ?? 0;


    //     // 10. Total Collection: total amount collected so far (all time)
    //     $totalCollection = Transaction::sum('amount_paid');

    //     // 11. Balance Due
    //     $balanceDue = $totalDemand - $totalCollection;

    //     // 12. Collection %
    //     $collectionPercentage = $totalDemand > 0 ? ($totalCollection / $totalDemand) * 100 : 0;

    //     $data = [
    //         'today_collection'                 => round($todaysCollection, 2),
    //         'monthly_demand_target'            => round($monthlyDemand, 2),
    //         'demand_till_date'                 => round($demandTillDate, 2),
    //         'demand_remaining'                 => round($demandRemaining, 2),
    //         'total_amount_received_this_month' => round($amountThisMonth, 2),
    //         'total_members_paid'               => $membersPaid,
    //         'unpaid_amount'                    => round($unpaidAmount, 2),
    //         'members_with_dues'                => $membersWithDues,
    //         'total_demand'                     => round($totalDemand, 2),
    //         'total_collection'                 => round($totalCollection, 2),
    //         'balance_due'                      => round($balanceDue, 2),
    //         'collection_percentage'            => round($collectionPercentage, 2),
    //     ];

    //     return responseMsg(true, "Dashboard Data", $data);
    // }

}
