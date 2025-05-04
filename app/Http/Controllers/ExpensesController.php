<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class ExpensesController extends Controller {
    public function index(Request $request) {
        $query = \App\Models\Expense::with(['expenseAccount', 'paymentAccount']);

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('expense_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('expense_date', '<=', $request->to_date);
        }
        // Account filter
        if ($request->filled('account_id')) {
            $query->where('expense_account_id', $request->account_id);
        }
        // Search (voucher no or description)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('voucher_number', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        }
        // Sorting
        $allowedSorts = ['created_at', 'date', 'amount'];
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        if (!in_array($sort, $allowedSorts)) $sort = 'created_at';
        if (!in_array($direction, ['asc','desc'])) $direction = 'desc';
        $expenses = $query->orderBy($sort, $direction)->paginate(15)->withQueryString();
        $accounts = \App\Models\ChartOfAccount::where('type', 'Expense')->orderBy('name')->get();
        return view('modules.expenses', compact('expenses', 'accounts'));
    }

    // AJAX endpoint for expenses table
    public function tableAjax(Request $request)
    {
        $query = \App\Models\Expense::with(['expenseAccount', 'paymentAccount']);
        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('expense_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('expense_date', '<=', $request->to_date);
        }
        // Account filter
        if ($request->filled('account_id')) {
            $query->where('expense_account_id', $request->account_id);
        }
        // Search (voucher no or description)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('voucher_number', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        }
        // Sorting
        $allowedSorts = ['created_at', 'date', 'amount'];
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        if (!in_array($sort, $allowedSorts)) $sort = 'created_at';
        if (!in_array($direction, ['asc','desc'])) $direction = 'desc';
        $expenses = $query->orderBy($sort, $direction)->paginate(15)->withQueryString();
        $html = view('expenses._expenses_table', ['expenses' => $expenses])->render();
        return response()->json(['html' => $html]);
    }
}
