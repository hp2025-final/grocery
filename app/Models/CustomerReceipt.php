<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerReceipt extends Model
{
    protected $fillable = [
        'receipt_number', // will be auto-generated
        'customer_id',
        'receipt_date',
        'amount_received',
        'payment_account_id',
        'bank_id',
        'payment_method',
        'reference',
        'notes',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function paymentAccount()
    {
        return $this->belongsTo(\App\Models\ChartOfAccount::class, 'payment_account_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
