@extends('layouts.app')

@section('title', 'ダッシュボード')
@section('page-title', 'ダッシュボード')

@section('header-actions')
    <span class="text-sm text-gray-500">{{ now()->format('Y年n月j日') }}</span>
@endsection

@section('content')

{{-- ─── KPIカード ─── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    @php
        $momChange = $kpi['revenue_last_month'] > 0
            ? round(($kpi['revenue_this_month'] - $kpi['revenue_last_month']) / $kpi['revenue_last_month'] * 100, 1)
            : null;
    @endphp

    {{-- 当月売上 --}}
    <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
        <p class="text-xs text-gray-500 mb-1">当月売上（税込）</p>
        <p class="text-2xl font-bold text-gray-900">¥{{ number_format($kpi['revenue_this_month']) }}</p>
        @if ($momChange !== null)
            <p class="text-xs mt-1 {{ $momChange >= 0 ? 'text-green-600' : 'text-red-500' }}">
                前月比 {{ $momChange >= 0 ? '+' : '' }}{{ $momChange }}%
            </p>
        @endif
    </div>

    {{-- 当月受注件数 --}}
    <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
        <p class="text-xs text-gray-500 mb-1">当月受注件数</p>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($kpi['orders_this_month']) }} 件</p>
    </div>

    {{-- 未払い請求 --}}
    <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
        <p class="text-xs text-gray-500 mb-1">未払い請求残高</p>
        <p class="text-2xl font-bold text-gray-900">¥{{ number_format($kpi['unpaid_amount']) }}</p>
    </div>

    {{-- 支払い遅延 --}}
    <div class="bg-white rounded-xl border border-{{ $kpi['overdue_count'] > 0 ? 'red' : 'gray' }}-200 px-5 py-4
                {{ $kpi['overdue_count'] > 0 ? 'bg-red-50' : 'bg-white' }} rounded-xl">
        <p class="text-xs text-gray-500 mb-1">入金遅延件数</p>
        <p class="text-2xl font-bold {{ $kpi['overdue_count'] > 0 ? 'text-red-600' : 'text-gray-900' }}">
            {{ $kpi['overdue_count'] }} 件
        </p>
        @if ($kpi['overdue_count'] > 0)
            <a href="{{ route('invoices.index', ['status' => 'overdue']) }}" class="text-xs text-red-500 underline mt-1 inline-block">一覧を見る</a>
        @endif
    </div>
</div>

{{-- ─── グラフ行 ─── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

    {{-- 月次売上グラフ --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">月次売上（過去12ヶ月）</h2>
        <canvas id="salesChart" height="100"></canvas>
    </div>

    {{-- ステータス別ドーナツ --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">受注ステータス内訳</h2>
        <canvas id="statusChart" height="180"></canvas>
        <ul class="mt-3 space-y-1">
            @foreach ($statusChart['labels'] as $i => $label)
                <li class="flex items-center gap-2 text-xs text-gray-600">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0"
                          style="background:{{ $statusChart['colors'][$i] }}"></span>
                    {{ $label }}：{{ $statusChart['data'][$i] }} 件
                </li>
            @endforeach
        </ul>
    </div>
</div>

{{-- ─── 下段2列 ─── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- 直近受注 --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700">直近の受注</h2>
            <a href="{{ route('orders.index') }}" class="text-xs text-blue-600 hover:underline">すべて見る</a>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                <tr>
                    <th class="px-4 py-2 text-left">受注番号</th>
                    <th class="px-4 py-2 text-left">顧客</th>
                    <th class="px-4 py-2 text-left">受注日</th>
                    <th class="px-4 py-2 text-right">金額</th>
                    <th class="px-4 py-2 text-center">状態</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($recentOrders as $order)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-2">
                            <a href="{{ route('orders.show', $order) }}" class="text-blue-600 hover:underline font-mono text-xs">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td class="px-4 py-2 text-gray-700">{{ $order->customer->name }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $order->order_date->format('m/d') }}</td>
                        <td class="px-4 py-2 text-right font-medium">¥{{ number_format($order->total_amount) }}</td>
                        <td class="px-4 py-2 text-center">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $order->status_color }}">
                                {{ $order->status_label }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400 text-sm">受注データがありません</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 売上TOP顧客 --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">売上TOP顧客</h2>
        </div>
        <ul class="divide-y divide-gray-100">
            @forelse ($topCustomers as $i => $row)
                <li class="px-5 py-3 flex items-center gap-3">
                    <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 text-xs font-bold flex items-center justify-center shrink-0">
                        {{ $i + 1 }}
                    </span>
                    <span class="flex-1 text-sm text-gray-800 truncate">{{ $row->customer->name }}</span>
                    <span class="text-sm font-medium text-gray-900">¥{{ number_format($row->total) }}</span>
                </li>
            @empty
                <li class="px-5 py-6 text-center text-gray-400 text-sm">データなし</li>
            @endforelse
        </ul>
    </div>

</div>

@endsection

@push('scripts')
<script>
// 月次売上グラフ
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
    type: 'bar',
    data: {
        labels: @json($salesChart['labels']),
        datasets: [{
            label: '売上（税込）',
            data: @json($salesChart['data']),
            backgroundColor: 'rgba(59,130,246,0.15)',
            borderColor: 'rgba(59,130,246,0.8)',
            borderWidth: 2,
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                ticks: {
                    callback: v => '¥' + v.toLocaleString(),
                    font: { size: 11 },
                },
                grid: { color: 'rgba(0,0,0,0.05)' }
            },
            x: { ticks: { font: { size: 11 } } }
        }
    }
});

// ステータスドーナツグラフ
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: @json($statusChart['labels']),
        datasets: [{
            data: @json($statusChart['data']),
            backgroundColor: @json($statusChart['colors']),
            borderWidth: 2,
            borderColor: '#fff',
        }]
    },
    options: {
        responsive: true,
        cutout: '65%',
        plugins: { legend: { display: false } }
    }
});
</script>
@endpush
