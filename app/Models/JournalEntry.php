<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_number', 'date', 'description', 'created_by', 'reference_type', 'reference_id'
    ];

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    /**
     * Check if the journal entry is balanced (total debit == total credit).
     * @return bool
     */
    public function isBalanced(): bool
    {
        $debit = $this->lines->sum('debit');
        $credit = $this->lines->sum('credit');
        return bccomp($debit, $credit, 2) === 0;
    }

    /**
     * Override save to enforce balancing rule.
     */
    public function save(array $options = [])
    {
        // If the model already exists, reload lines after save
        $result = parent::save($options);
        // Only validate if lines are loaded (eager loaded or after save)
        if ($this->relationLoaded('lines') && !$this->isBalanced()) {
            throw new Exception('Journal entry is not balanced. Total debits and credits must match.');
        }
        return $result;
    }
}
