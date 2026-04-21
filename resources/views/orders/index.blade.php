@extends('layouts.app')

@section('title', '受注管理')
@section('page-title', '受注管理')

@section('header-actions')
    <a href="{{ route('orders.create') }}"
       class="inline-flex items-center gap-1.5 bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        新規受注
    </a>
@endsection

@section('content')

{{-- ─── 検索フォーム ─── --}}
<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-4 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-500 mb-1">キーワード</label>
        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="受注番号・顧客名"
               class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm w-44 focus:outline-none focus:ring-2 focus:ring-blue-400">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">ステータス</label>
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">すべて</option>
            @foreach (\App\Models\Order::STATUS_LABELS as $val => $label)
                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">顧客</label>
        <select name="customer_id" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">すべて</option>
            @foreach ($customers as $c)
                <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">受注日（from）</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}"
               class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">受注日（to）</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}"
               class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
    </div>
    <button type="submit" class="bg-gray-800 text-white text-sm px-4 py-1.5 rounded-lg hover:bg-gray-700 transition">検索</button>
    <a href="{{ route('orders.index') }}" class="text-sm text-gray-500 hover:underline py-1.5">リセット</a>
</form>

{{-- ─── テーブル ─── --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide border-b border-gray-200">
            <tr>
                <th class="px-4 py-3 text-left">受注番号</th>
                <th class="px-4 py-3 text-left">顧客</th>
                <th class="px-4 py-3 text-left">受注日</th>
                <th class="px-4 py-3 text-left">納品日</th>
                <th class="px-4 py-3 text-right">税込合計</th>
                <th class="px-4 py-3 text-center">ステータス</th>
                <th class="px-4 py-3 text-center">請求書</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($orders as $order)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <a href="{{ route('orders.show', $order) }}" class="text-blue-600 hover:underline font-mono text-xs">
                            {{ $order->order_number }}
                        </a>
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $order->customer->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $order->order_date->format('Y/m/d') }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $order->delivery_date?->format('Y/m/d') ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-medium">¥{{ number_format($order->total_amount) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $order->status_color }}">
                            {{ $order->status_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if ($order->invoice)
                            <a href="{{ route('invoices.show', $order->invoice) }}" class="text-xs text-green-600 hover:underline">
                                {{ $order->invoice->invoice_number }}
                            </a>
                        @elseif ($order->can_invoice)
                            <span class="text-xs text-gray-400">未発行</span>
                        @else
                            <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('orders.edit', $order) }}" class="text-xs text-gray-500 hover:text-gray-800 hover:underline">編集</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-10 text-center text-gray-400">受注が見つかりません</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($orders->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $orders->links() }}
        </div>
    @endif
</div>

@endsection
