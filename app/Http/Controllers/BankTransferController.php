<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bank;

class BankTransferController extends Controller
{
    public function create(Request $request)
    {
        $bankAccounts = Bank::all();
        
        $query = \App\Models\BankTransfer::with(['fromBank', 'toBank'])
            ->orderByDesc('date')
            ->orderByDesc('id');
            
        // Enhanced search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', '%'.$search.'%')
                  ->orWhereHas('fromBank', function($q) use ($search) {
                      $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('account_number', 'like', '%'.$search.'%');
                  })
                  ->orWhereHas('toBank', function($q) use ($search) {
                      $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('account_number', 'like', '%'.$search.'%');
                  });
            });
        }
        
        if ($request->has('from_date')) {
            $query->where('date', '>=', $request->from_date);
        }
        
        if ($request->has('to_date')) {
            $query->where('date', '<=', $request->to_date);
        }
        
        $recentTransfers = $query->paginate(10)->withQueryString();
        
        return view('bank_transfers.create', compact('bankAccounts', 'recentTransfers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_account_id' => 'required|different:to_account_id|exists:banks,id',
            'to_account_id' => 'required|exists:banks,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'required|string',
        ]);

        // Get chart_of_accounts_id for both banks
        $fromBank = \App\Models\Bank::findOrFail($validated['from_account_id']);
        $toBank = \App\Models\Bank::findOrFail($validated['to_account_id']);
        $fromAccountId = $fromBank->account_id;
        $toAccountId = $toBank->account_id;

        // Create the bank transfer record
        $transfer = \App\Models\BankTransfer::create([
            'from_bank_id' => $fromBank->id,
            'to_bank_id' => $toBank->id,
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'description' => $validated['description'],
            'created_by' => auth()->id(),
        ]);

        // Create the journal entry
        $journalEntry = \App\Models\JournalEntry::create([
            'entry_number' => uniqid('JV'),
            'date' => $validated['date'],
            'description' => 'Bank transfer: ' . $fromBank->name . ' → ' . $toBank->name . ($validated['description'] ? ' - ' . $validated['description'] : ''),
            'reference_type' => 'bank_transfer',
            'reference_id' => $transfer->id,
            'created_by' => auth()->id(),
        ]);

        // Create journal entry lines (double entry)
        \App\Models\JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $toAccountId,
            'debit' => $validated['amount'],
            'credit' => null,
            'description' => 'Bank Transfer In',
        ]);
        \App\Models\JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $fromAccountId,
            'debit' => null,
            'credit' => $validated['amount'],
            'description' => 'Bank Transfer Out',
        ]);

        // Link the journal entry to the transfer
        $transfer->journal_entry_id = $journalEntry->id;
        $transfer->save();

        return redirect()->route('bank_transfers.create')->with('success', 'Bank transfer recorded successfully.');
    }

    public function destroy(\App\Models\BankTransfer $bank_transfer)
    {
        // Save journal entry id before deleting transfer
        $journalEntryId = $bank_transfer->journal_entry_id;

        // Delete the bank transfer first
        $bank_transfer->delete();

        // Then delete the linked journal entry (cascades journal_entry_lines)
        if ($journalEntryId) {
            \App\Models\JournalEntry::where('id', $journalEntryId)->delete();
        }

        return redirect()->back()->with('success', 'Bank transfer deleted successfully.');
    }

    public function liveSearch(Request $request)
    {
        try {
            $query = \App\Models\BankTransfer::with(['fromBank', 'toBank'])
                ->orderByDesc('date')
                ->orderByDesc('id');
                
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('description', 'like', '%'.$search.'%')
                      ->orWhereHas('fromBank', function($q) use ($search) {
                          $q->where('name', 'like', '%'.$search.'%')
                            ->orWhere('account_number', 'like', '%'.$search.'%');
                      })
                      ->orWhereHas('toBank', function($q) use ($search) {
                          $q->where('name', 'like', '%'.$search.'%')
                            ->orWhere('account_number', 'like', '%'.$search.'%');
                      });
                });
            }
            
            if ($request->has('from_date') && $request->from_date) {
                $query->where('date', '>=', $request->from_date);
            }
            
            if ($request->has('to_date') && $request->to_date) {
                $query->where('date', '<=', $request->to_date);
            }
            
            $transfers = $query->paginate(10);
            
            return response()->json([
                'html' => view('bank_transfers._transfers_table', ['entries' => $transfers])->render()
            ]);
        } catch (\Exception $e) {
            \Log::error('Bank transfer live search error: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }
}
