<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerListController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $customers = Customer::query();
        if ($search) {
            $customers->where('name', 'like', "%$search%");
        }
        $customers = $customers->orderBy('name')->paginate(20);
        return view('reports.customer_list', [
            'customers' => $customers,
            'search' => $search,
        ]);
    }
}
