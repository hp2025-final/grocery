<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JournalEntry;

class JournalReportController extends Controller
{
    public function index(Request $request)
    {
        $query = JournalEntry::with(['lines.account']);
        $from = $request->input('from');
        $to = $request->input('to');
        if ($from) {
            $query->whereDate('date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('date', '<=', $to);
        }
        $entries = $query->orderByDesc('date')->orderByDesc('id')->paginate(20);
        return view('reports.journal', compact('entries', 'from', 'to'));
    }
}
