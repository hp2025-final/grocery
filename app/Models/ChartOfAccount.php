<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'type', 'parent_id', 'is_group', 'opening_balance'
    ];

    public function journalEntryLines()
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    // Add: Relationship to Bank (if this account is a bank account)
    public function bank()
    {
        return $this->hasOne(\App\Models\Bank::class, 'account_id');
    }
}
