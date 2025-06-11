<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        $mTransaction->create($req);
    }
}
