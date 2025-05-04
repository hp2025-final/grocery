<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = [];
    
    public function fromBank()
    {
        return $this->belongsTo(Bank::class, 'from_bank_id');
    }
    
    public function toBank()
    {
        return $this->belongsTo(Bank::class, 'to_bank_id');
    }
    
    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }
    
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
