<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * | Add Transaction Details
     */
    public function store($req)
    {
        $mTransaction = new Transaction();
        $mTransaction->create($req);
    }
}
