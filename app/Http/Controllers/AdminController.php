<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\Vendor;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function index()
    {
        return view('admin.index');    }    /**
     * Show the sales form copy page
     */
    public function salesFormCopy()
    {
        $customers = Customer::orderBy('name')->get();
        $products = Inventory::orderBy('name')->get()->map(function($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'unit_name' => $p->unit,
                'sale_price' => $p->sale_price,
            ];
        })->values()->toArray();
        
        return view('admin.sales-form-copy', compact('customers', 'products'));
    }
    
    /**
     * Show the purchase form copy page
     */
    public function purchaseFormCopy()
    {
        $vendors = \App\Models\Vendor::orderBy('name')->get();
        $products = Inventory::orderBy('name')->get()->map(function($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'unit_name' => $p->unit,
                'buy_price' => $p->buy_price,
            ];
        })->values()->toArray();
        
        return view('admin.purchase-form-copy', compact('vendors', 'products'));
    }
}
