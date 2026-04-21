@extends('layouts.app')

@section('title', '請求書：' . $invoice->invoice_number)
@section('page-title', '請求書詳細')

@section('header-actions')
    <a href="{{ route('invoices.index') }}" class="text-sm text-gray-500 hover:underline">← 一覧へ</a>
    <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank"
       class="text-sm bg-white border border-gray-300 text-gray-700 px-4 py-1.5 rounded-lg hover:bg-gray-50 transition">
        PDFダウンロード
    </a>
    @if ($invoice->status === 'draft')
        <form method="POST" action="{{ route('invoices.send', $invoice) }}" class="inline">
            @csrf
            <button type="submit" onclick="return confirm('請求書メールを送付しますか？')"
                    class="text-sm bg-blue-600 text-white px-4 py-1.5 rounded-lg hover:bg-blue-700 transition">
                メール送付
            </button>
        </form>
    @endif
    @if (in_array($invoice->status, ['sent', 'overdue']))
        <form method="POST" action="{{ route('invoices.paid', $invoice) }}" class="inline">
            @csrf
            <button type="submit" onclick="return confirm('入金確認済みにしますか？')"
                    class="text-sm bg-green-600 text-white px-4 py-1.5 rounded-lg hover:bg-green-700 transition">
                入金確認
            </button>
        </form>
    @endif
@endsection

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- ─── 左：メタ情報 ─── --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-400 text-xs">請求書番号</span>
                <span class="font-mono font-semibold">{{ $invoice->invoice_number }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400 text-xs">ステータス</span>
                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $invoice->status_color }}">
                    {{ $invoice->status_label }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400 text-xs">顧客</span>
                <span class="font-medium">{{ $invoice->customer->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400 text-xs">発行日</span>
                <span>{{ $invoice->issue_date->format('Y年m月d日') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400 text-xs">支払期限</span>
                <span class="{{ $invoice->is_overdue ? 'text-red-600 font-semibold' : '' }}">
                    {{ $invoice->due_date->format('Y年m月d日') }}
                </span>
            </div>
            @if ($invoice->sent_at)
            <div class="flex justify-between">
                <span class="text-gray-400 text-xs">送付日時</span>
                <span>{{ $invoice->sent_at->format('Y/m/d H:i') }}</span>
            </div>
            @endif
            @if ($invoice->paid_at)
            <div class="flex justify-between">
                <span class="text-gray-400 text-xs">入金確認日</span>
                <span class="text-green-600 font-medium">{{ $invoice->paid_at->format('Y年m月d日') }}</span>
            </div>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 text-sm">
            <p class="text-xs text-gray-400 mb-1">対応受注</p>
            <a href="{{ route('orders.show', $invoice->order) }}" class="text-blue-600 hover:underline font-mono text-xs">
                {{ $invoice->order->order_number }}
            </a>
        </div>
    </div>

    {{-- ─── 右：明細 ─── --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">請求明細</h2>
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
                    @foreach ($invoice->order->items as $item)
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
                    <span class="w-32 text-right">¥{{ number_format($invoice->subtotal) }}</span>
                </div>
                <div class="flex gap-12 text-gray-500">
                    <span>消費税</span>
                    <span class="w-32 text-right">¥{{ number_format($invoice->tax_amount) }}</span>
                </div>
                <div class="flex gap-12 font-bold text-gray-900 text-base border-t border-gray-200 pt-2 mt-1">
                    <span>合計（税込）</span>
                    <span class="w-32 text-right">¥{{ number_format($invoice->total_amount) }}</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
