<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappLog extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function createLog($request)
    {
        return WhatsappLog::create($request);
    }
}
