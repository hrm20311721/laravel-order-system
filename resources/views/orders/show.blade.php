@extends('layouts.app')

@section('title', '受注詳細：' . $order->order_number)
@section('page-title', '受注詳細')

@section('header-actions')
    <a href="{{ route('orders.index') }}" class="text-sm text-gray-500 hover:underline">← 一覧へ</a>
    @if (!in_array($order->status, ['completed', 'cancelled']))
        <a href="{{ route('orders.edit', $order) }}"
           class="text-sm bg-white border border-gray-300 text-gray-700 px-4 py-1.5 rounded-lg hover:bg-gray-50 transition">
            編集
        </a>
    @endif
    @if ($order->can_invoice)
        <button onclick="document.getElementById('invoice-modal').classList.remove('hidden')"
                class="text-sm bg-blue-600 text-white px-4 py-1.5 rounded-lg hover:bg-blue-700 transition">
            請求書を発行
        </button>
    @elseif ($order->invoice)
        <a href="{{ route('invoices.show', $order->invoice) }}"
           class="text-sm bg-green-600 text-white px-4 py-1.5 rounded-lg hover:bg-green-700 transition">
            請求書を見る
        </a>
    @endif
@endsection

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- ─── 左：基本情報 ─── --}}
    <div class="space-y-4">

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400">受注番号</span>
                <span class="font-mono text-sm font-semibold">{{ $order->order_number }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400">ステータス</span>
                <span class="inline-block px-3 py-0.5 rounded-full text-xs font-medium {{ $order->status_color }}">
                    {{ $order->status_label }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400">顧客</span>
                <span class="text-sm font-medium">{{ $order->customer->name }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400">受注日</span>
                <span class="text-sm">{{ $order->order_date->format('Y年m月d日') }}</span>
            </div>
            @if ($order->delivery_date)
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400">納品希望日</span>
                <span class="text-sm">{{ $order->delivery_date->format('Y年m月d日') }}</span>
            </div>
            @endif
            @if ($order->shipped_date)
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400">出荷日</span>
                <span class="text-sm">{{ $order->shipped_date->format('Y年m月d日') }}</span>
            </div>
            @endif
        </div>

        {{-- ステータス変更 --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500 mb-3">ステータスを変更</p>
            <form method="POST" action="{{ route('orders.status', $order) }}">
                @csrf @method('PATCH')
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    @foreach (\App\Models\Order::STATUS_LABELS as $val => $label)
                        <option value="{{ $val }}" {{ $order->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="w-full bg-gray-800 text-white text-sm py-2 rounded-lg hover:bg-gray-700 transition">
                    更新する
                </button>
            </form>
        </div>

        @if ($order->notes)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500 mb-2">備考</p>
            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $order->notes }}</p>
        </div>
        @endif

    </div>

    {{-- ─── 右：明細・合計 ─── --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">受注明細</h2>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left">商品名</th>
                        <th class="px-4 py-2 text-right">単価</th>
                        <th class="px-4 py-2 text-right">数量</th>
                        <th class="px-4 py-2 text-center">税率</th>
                        <th class="px-4 py-2 text-right">小計(税抜)</th>
                        <th class="px-4 py-2 text-right">小計(税込)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="px-4 py-3">{{ $item->product_name }}</td>
                            <td class="px-4 py-3 text-right">¥{{ number_format($item->unit_price) }}</td>
                            <td class="px-4 py-3 text-right">{{ $item->quantity }} {{ $item->unit }}</td>
                            <td class="px-4 py-3 text-center text-gray-500">{{ $item->tax_rate }}%</td>
                            <td class="px-4 py-3 text-right">¥{{ number_format($item->line_subtotal) }}</td>
                            <td class="px-4 py-3 text-right font-medium">¥{{ number_format($item->line_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-4 border-t border-gray-100 flex flex-col items-end gap-1 text-sm bg-gray-50">
                <div class="flex gap-12 text-gray-500">
                    <span>小計（税抜）</span>
                    <span class="w-32 text-right">¥{{ number_format($order->subtotal) }}</span>
                </div>
                <div class="flex gap-12 text-gray-500">
                    <span>消費税</span>
                    <span class="w-32 text-right">¥{{ number_format($order->tax_amount) }}</span>
                </div>
                <div class="flex gap-12 font-bold text-gray-900 text-base border-t border-gray-200 pt-2 mt-1">
                    <span>合計（税込）</span>
                    <span class="w-32 text-right">¥{{ number_format($order->total_amount) }}</span>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ─── 請求書発行モーダル ─── --}}
<div id="invoice-modal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl">
        <h3 class="text-base font-semibold text-gray-800 mb-4">請求書を発行</h3>
        <form method="POST" action="{{ route('invoices.create-from-order', $order) }}">
            @csrf
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">発行日 <span class="text-red-500">*</span></label>
                    <input type="date" name="issue_date" required value="{{ today()->format('Y-m-d') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">支払期限 <span class="text-red-500">*</span></label>
                    <input type="date" name="due_date" required value="{{ today()->addDays(30)->format('Y-m-d') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">備考</label>
                    <textarea name="notes" rows="2"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-5">
                <button type="button" onclick="document.getElementById('invoice-modal').classList.add('hidden')"
                        class="text-sm text-gray-500 hover:underline">キャンセル</button>
                <button type="submit" class="bg-blue-600 text-white text-sm px-5 py-2 rounded-lg hover:bg-blue-700 transition">
                    発行する
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
