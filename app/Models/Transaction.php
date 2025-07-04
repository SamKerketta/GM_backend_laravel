<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function scopeActive($query)
    {
        return $query->where('transactions.status', 1);
    }


    // Format payment_date as d-m-Y
    public function getPaymentDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d-m-Y') : null;
    }

    // Format month_from as d-m-Y
    public function getMonthFromAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d-m-Y') : null;
    }

    // Format month_till as d-m-Y
    public function getMonthTillAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d-m-Y') : null;
    }

    /**
     * | Add Transaction Details
     */
    public function store($req)
    {
        $mTransaction = new Transaction();
        return $mTransaction->create($req);
    }
    /**
     * | Get Transaction Details
     */
    public function getTransactionDetails($transactionId)
    {
        $tranDetails = Transaction::select(
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
        )
            ->join('members', 'members.id', 'transactions.member_id')
            ->join('plan_masters', 'plan_masters.id', 'members.plan_id')
            ->where('transactions.id', $transactionId)
            ->first();

        return $tranDetails;
    }
}
