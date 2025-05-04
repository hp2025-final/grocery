<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'adjustment_number', 'adjustment_date', 'adjustment_type', 'notes'
    ];

    public function items()
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }
}
