<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction;

class Bank extends Model
{

    protected $fillable = [
        'name', 'branch', 'account_number', 'account_title', 'iban', 'swift_code', 'notes', 'opening_balance', 'account_id'
    ];
    public function account()
    {
        return $this->belongsTo(\App\Models\ChartOfAccount::class, 'account_id');
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'bank_id');
    }
    public function outgoingTransactions()
    {
        return $this->hasMany(Transaction::class, 'from_bank_id');
    }
    
    public function incomingTransactions()
    {
        return $this->hasMany(Transaction::class, 'to_bank_id');
    }
}
