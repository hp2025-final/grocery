
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\JournalEntryLine;
use Carbon\Carbon;

class CustomerReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $search = $request->input('search');

        $customers = Customer::query();
        if ($search) {
            $customers->where('name', 'like', "%$search%");
        }
        $customers = $customers->orderBy('name')->get();

        $rows = [];
        foreach ($customers as $customer) {
            // Opening Balance: sum of all debits - credits before $from
            $openingDebit = JournalEntryLine::whereHas('journalEntry', function ($q) use ($customer, $from) {
                $q->where('reference_type', 'customer')->where('reference_id', $customer->id);
                if ($from) $q->where('date', '<', $from);
            })->sum('debit');
            $openingCredit = JournalEntryLine::whereHas('journalEntry', function ($q) use ($customer, $from) {
                $q->where('reference_type', 'customer')->where('reference_id', $customer->id);
                if ($from) $q->where('date', '<', $from);
            })->sum('credit');
            $opening = $openingDebit - $openingCredit;

            // Period Debit/Credit
            $periodDebit = JournalEntryLine::whereHas('journalEntry', function ($q) use ($customer, $from, $to) {
                $q->where('reference_type', 'customer')->where('reference_id', $customer->id);
                if ($from) $q->where('date', '>=', $from);
                if ($to) $q->where('date', '<=', $to);
            })->sum('debit');
            $periodCredit = JournalEntryLine::whereHas('journalEntry', function ($q) use ($customer, $from, $to) {
                $q->where('reference_type', 'customer')->where('reference_id', $customer->id);
                if ($from) $q->where('date', '>=', $from);
                if ($to) $q->where('date', '<=', $to);
            })->sum('credit');

            $closing = $opening + $periodDebit - $periodCredit;

            $rows[] = [
                'customer' => $customer,
                'opening' => $opening,
                'debit' => $periodDebit,
                'credit' => $periodCredit,
                'closing' => $closing,
            ];
        }

        return view('reports.customers', [
            'rows' => $rows,
            'from' => $from,
            'to' => $to,
            'search' => $search,
        ]);
    }
}
