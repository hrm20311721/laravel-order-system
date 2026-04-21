<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService) {}

    public function index()
    {
        $kpi           = $this->dashboardService->getKpi();
        $salesChart    = $this->dashboardService->getMonthlySalesChart();
        $statusChart   = $this->dashboardService->getStatusBreakdown();
        $recentOrders  = $this->dashboardService->getRecentOrders();
        $topCustomers  = $this->dashboardService->getTopCustomers();

        return view('dashboard.index', compact(
            'kpi', 'salesChart', 'statusChart', 'recentOrders', 'topCustomers'
        ));
    }
}
