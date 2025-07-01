<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\Vendor;
use App\Models\InventoryCategory;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function index()
    {
        return view('admin.index');
    }

    /**
     * Show the sales form copy page
     */
    public function salesFormCopy()
    {
        $user = auth()->user();
        // Only allow users with view permission
        if (!$user || !$this->hasPermission($user, 'admin.sales-form-copy.view')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view this page.');
        }

        $customers = Customer::orderBy('name')->get();
        $categories = InventoryCategory::orderBy('name')->get();
        $products = Inventory::with('category')
            ->orderBy('name')
            ->get()
            ->map(function($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'unit_name' => $p->unit,
                    'sale_price' => $p->sale_price,
                    'category_id' => $p->category_id,
                    'category_name' => $p->category ? $p->category->name : null,
                ];
            })
            ->values()
            ->toArray();

        // Pass CRUD permissions to the view
        $canCreate = $this->hasPermission($user, 'admin.sales-form-copy.create');
        $canEdit = $this->hasPermission($user, 'admin.sales-form-copy.edit');
        $canDelete = $this->hasPermission($user, 'admin.sales-form-copy.delete');

        return view('admin.sales-form-copy', compact('customers', 'products', 'categories', 'canCreate', 'canEdit', 'canDelete'));
    }
    
    /**
     * Show the purchase form copy page
     */
    public function purchaseFormCopy()
    {
        $user = auth()->user();
        // Only allow users with view permission
        if (!$user || !$this->hasPermission($user, 'admin.purchase-form-copy.view')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view this page.');
        }

        $vendors = \App\Models\Vendor::orderBy('name')->get();
        $categories = InventoryCategory::orderBy('name')->get();
        $products = Inventory::with('category')
            ->orderBy('name')
            ->get()
            ->map(function($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'unit_name' => $p->unit,
                    'buy_price' => $p->buy_price,
                    'category_id' => $p->category_id,
                    'category_name' => $p->category ? $p->category->name : null,
                ];
            })->values()->toArray();

        // Pass CRUD permissions to the view
        $canCreate = $this->hasPermission($user, 'admin.purchase-form-copy.create');
        $canEdit = $this->hasPermission($user, 'admin.purchase-form-copy.edit');
        $canDelete = $this->hasPermission($user, 'admin.purchase-form-copy.delete');

        return view('admin.purchase-form-copy', compact('vendors', 'products', 'categories', 'canCreate', 'canEdit', 'canDelete'));
    }

    // Helper to check permission for current user
    private function hasPermission($user, $permission)
    {
        if (!$user) return false;
        if (in_array($user->email, config('superadmins.emails', []))) return true;
        $permissions = \Illuminate\Support\Facades\Storage::exists('user_permissions.json')
            ? json_decode(\Illuminate\Support\Facades\Storage::get('user_permissions.json'), true)
            : [];
        return in_array($permission, $permissions[$user->email]['permissions'] ?? []);
    }
}
