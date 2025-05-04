<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_number', 'expense_date', 'expense_category_id', 'amount', 'payment_method', 'payment_account_id', 'reference', 'notes'
    ];

    public function expenseAccount()
    {
        return $this->belongsTo(\App\Models\ChartOfAccount::class, 'expense_account_id');
    }

    public function category()
    {
        return $this->belongsTo(\App\Models\ChartOfAccount::class, 'expense_category_id');
    }

    public function paymentAccount()
    {
        return $this->belongsTo(\App\Models\ChartOfAccount::class, 'payment_account_id');
    }
}
