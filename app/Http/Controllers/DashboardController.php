<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardService $dashboardService)
    {
        $period = $request->get('period', 'today');
        $kpis = $dashboardService->getKPIs($period);
        return view('dashboard', compact('kpis'));
    }

    // AJAX: KPI Data
    public function kpis(Request $request, DashboardService $dashboardService)
    {
        try {
            $period = $request->get('period', 'today');
            $kpis = $dashboardService->getKPIs($period);
            return response()->json($kpis);
        } catch (\Exception $e) {
            \Log::error('Error in kpis: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // AJAX: Journal Entries
    public function journalEntries(Request $request, DashboardService $dashboardService)
    {
        $period = $request->get('period', 'today');
        $page = (int) $request->get('page', 1);
        $result = $dashboardService->getRecentJournalEntries($period, $page);
        return response()->json($result);
    }

    // AJAX: Sale Chart Data
    public function saleChart(Request $request, DashboardService $dashboardService)
    {
        try {
            $period = $request->get('period', 'today');
            $data = $dashboardService->getSaleChartData($period);
            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Error in saleChart: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
