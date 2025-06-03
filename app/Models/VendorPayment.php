<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VendorPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number', 'vendor_id', 'payment_date', 'amount_paid', 'payment_method', 'payment_account_id', 'reference', 'notes', 'user_id'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function paymentAccount()
    {
        return $this->belongsTo(\App\Models\ChartOfAccount::class, 'payment_account_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
