<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'email', 'address', 'opening_balance', 'opening_type', 'account_id'
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function payments()
    {
        return $this->hasMany(VendorPayment::class);
    }

    public function account()
    {
        return $this->belongsTo(\App\Models\ChartOfAccount::class, 'account_id');
    }
}
