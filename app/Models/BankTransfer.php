<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_bank_id', 'to_bank_id', 'amount', 'date', 'description', 'journal_entry_id', 'created_by'
    ];

    public function fromBank() {
        return $this->belongsTo(Bank::class, 'from_bank_id');
    }
    public function toBank() {
        return $this->belongsTo(Bank::class, 'to_bank_id');
    }
    public function journalEntry() {
        return $this->belongsTo(JournalEntry::class);
    }
    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }
}
