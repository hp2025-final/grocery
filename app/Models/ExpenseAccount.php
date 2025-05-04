<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseAccount extends Model
{
    protected $fillable = [
        'name', 'account_id'
    ];

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
