<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    /**
     * KPIサマリーを返す
     */
    public function getKpi(): array
    {
        $today = now();

        return [
            // 当月受注件数
            'orders_this_month' => Order::thisMonth()->count(),

            // 当月売上（税込）
            'revenue_this_month' => Order::thisMonth()
                ->whereNotIn('status', ['cancelled', 'quote'])
                ->sum('total_amount'),

            // 未払い請求書合計（送付済）
            'unpaid_amount' => Invoice::unpaid()->sum('total_amount'),

            // 期限切れ請求書件数
            'overdue_count' => Invoice::where('status', 'overdue')->count(),

            // 前月比（売上）
            'revenue_last_month' => Order::whereMonth('order_date', $today->copy()->subMonth()->month)
                ->whereYear('order_date', $today->copy()->subMonth()->year)
                ->whereNotIn('status', ['cancelled', 'quote'])
                ->sum('total_amount'),
        ];
    }

    /**
     * 過去12ヶ月の月次売上データ（Chart.js 用）
     */
    public function getMonthlySalesChart(): array
    {
        $months = collect(range(11, 0))->map(fn($i) => now()->subMonths($i));

        $sales = Order::select(
                DB::raw('YEAR(order_date) as year'),
                DB::raw('MONTH(order_date) as month'),
                DB::raw('SUM(total_amount) as total')
            )
            ->whereNotIn('status', ['cancelled', 'quote'])
            ->where('order_date', '>=', now()->subMonths(12)->startOfMonth())
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(fn($r) => "{$r->year}-{$r->month}");

        return [
            'labels' => $months->map(fn($m) => $m->format('Y年n月'))->values()->toArray(),
            'data'   => $months->map(function ($m) use ($sales) {
                $key = "{$m->year}-{$m->month}";
                return (int) ($sales->get($key)?->total ?? 0);
            })->values()->toArray(),
        ];
    }

    /**
     * ステータス別受注件数（ドーナツグラフ用）
     */
    public function getStatusBreakdown(): array
    {
        $counts = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $labels = \App\Models\Order::STATUS_LABELS;
        $colors = [
            'quote'     => '#6B7280',
            'ordered'   => '#3B82F6',
            'shipping'  => '#F59E0B',
            'completed' => '#10B981',
            'cancelled' => '#EF4444',
        ];

        return [
            'labels'     => collect($labels)->values()->toArray(),
            'data'       => collect($labels)->keys()->map(fn($k) => $counts->get($k, 0))->values()->toArray(),
            'colors'     => collect($labels)->keys()->map(fn($k) => $colors[$k])->values()->toArray(),
        ];
    }

    /**
     * 直近受注 10件
     */
    public function getRecentOrders(): \Illuminate\Support\Collection
    {
        return Order::with('customer')
            ->latest('order_date')
            ->limit(10)
            ->get();
    }

    /**
     * 売上上位顧客 Top5
     */
    public function getTopCustomers(): \Illuminate\Support\Collection
    {
        return Order::select('customer_id', DB::raw('SUM(total_amount) as total'))
            ->whereNotIn('status', ['cancelled', 'quote'])
            ->with('customer:id,name')
            ->groupBy('customer_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
    }
}
