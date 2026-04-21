@extends('layouts.app')

@section('title', '請求書一覧')
@section('page-title', '請求書一覧')

@section('content')

<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-4 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-500 mb-1">キーワード</label>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="請求書番号・顧客名"
               class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm w-44 focus:outline-none focus:ring-2 focus:ring-blue-400">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">ステータス</label>
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">すべて</option>
            @foreach (\App\Models\Invoice::STATUS_LABELS as $val => $label)
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
    <button type="submit" class="bg-gray-800 text-white text-sm px-4 py-1.5 rounded-lg hover:bg-gray-700 transition">検索</button>
    <a href="{{ route('invoices.index') }}" class="text-sm text-gray-500 hover:underline py-1.5">リセット</a>
</form>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide border-b border-gray-200">
            <tr>
                <th class="px-4 py-3 text-left">請求書番号</th>
                <th class="px-4 py-3 text-left">顧客</th>
                <th class="px-4 py-3 text-left">発行日</th>
                <th class="px-4 py-3 text-left">支払期限</th>
                <th class="px-4 py-3 text-right">金額（税込）</th>
                <th class="px-4 py-3 text-center">ステータス</th>
                <th class="px-4 py-3 text-center">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($invoices as $invoice)
                <tr class="hover:bg-gray-50 transition {{ $invoice->is_overdue ? 'bg-red-50' : '' }}">
                    <td class="px-4 py-3">
                        <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:underline font-mono text-xs">
                            {{ $invoice->invoice_number }}
                        </a>
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $invoice->customer->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $invoice->issue_date->format('Y/m/d') }}</td>
                    <td class="px-4 py-3 {{ $invoice->is_overdue ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                        {{ $invoice->due_date->format('Y/m/d') }}
                        @if ($invoice->is_overdue) <span class="text-xs ml-1">期限切れ</span> @endif
                    </td>
                    <td class="px-4 py-3 text-right font-medium">¥{{ number_format($invoice->total_amount) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $invoice->status_color }}">
                            {{ $invoice->status_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center flex items-center justify-center gap-2">
                        <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank"
                           class="text-xs text-gray-500 hover:text-gray-800">PDF</a>
                        @if ($invoice->status === 'draft')
                            <form method="POST" action="{{ route('invoices.send', $invoice) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-blue-600 hover:underline"
                                        onclick="return confirm('メールを送付しますか？')">送付</button>
                            </form>
                        @endif
                        @if (in_array($invoice->status, ['sent', 'overdue']))
                            <form method="POST" action="{{ route('invoices.paid', $invoice) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-green-600 hover:underline"
                                        onclick="return confirm('入金確認済みにしますか？')">入金確認</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-gray-400">請求書が見つかりません</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($invoices->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $invoices->links() }}</div>
    @endif
</div>

@endsection
