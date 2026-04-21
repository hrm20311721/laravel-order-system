@extends('layouts.app')

@section('title', isset($order) ? '受注編集' : '新規受注')
@section('page-title', isset($order) ? '受注編集：' . $order->order_number : '新規受注')

@section('content')

<form method="POST"
      action="{{ isset($order) ? route('orders.update', $order) : route('orders.store') }}"
      id="order-form">
    @csrf
    @if (isset($order)) @method('PUT') @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- ─── 左：基本情報 ─── --}}
        <div class="lg:col-span-1 space-y-4">

            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
                <h2 class="text-sm font-semibold text-gray-700">基本情報</h2>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">顧客 <span class="text-red-500">*</span></label>
                    <select name="customer_id" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">選択してください</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}"
                                    data-email="{{ $c->email }}"
                                    {{ old('customer_id', $order->customer_id ?? '') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">ステータス</label>
                    <select name="status"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        @foreach (\App\Models\Order::STATUS_LABELS as $val => $label)
                            <option value="{{ $val }}"
                                {{ old('status', $order->status ?? 'ordered') === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">受注日 <span class="text-red-500">*</span></label>
                    <input type="date" name="order_date" required
                           value="{{ old('order_date', isset($order) ? $order->order_date->format('Y-m-d') : today()->format('Y-m-d')) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">納品希望日</label>
                    <input type="date" name="delivery_date"
                           value="{{ old('delivery_date', isset($order) ? $order->delivery_date?->format('Y-m-d') : '') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">配送先住所（顧客と異なる場合）</label>
                    <textarea name="shipping_address" rows="2"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">{{ old('shipping_address', $order->shipping_address ?? '') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">備考</label>
                    <textarea name="notes" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">{{ old('notes', $order->notes ?? '') }}</textarea>
                </div>
            </div>

        </div>

        {{-- ─── 右：明細 ─── --}}
        <div class="lg:col-span-2 space-y-4">

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-700">受注明細</h2>
                    <button type="button" id="add-row"
                            class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                        + 行を追加
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm" id="items-table">
                        <thead class="text-xs text-gray-500 border-b border-gray-200">
                            <tr>
                                <th class="pb-2 text-left w-8">#</th>
                                <th class="pb-2 text-left">商品</th>
                                <th class="pb-2 text-right w-24">単価</th>
                                <th class="pb-2 text-right w-16">数量</th>
                                <th class="pb-2 text-center w-20">税率</th>
                                <th class="pb-2 text-right w-28">小計(税抜)</th>
                                <th class="pb-2 w-8"></th>
                            </tr>
                        </thead>
                        <tbody id="items-body">
                            @php
                                $existingItems = old('items', isset($order) ? $order->items->toArray() : [[]]);
                            @endphp
                            @foreach ($existingItems as $i => $item)
                                @include('orders._item_row', ['i' => $i, 'item' => $item])
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- 合計 --}}
                <div class="mt-4 pt-4 border-t border-gray-100 flex flex-col items-end gap-1 text-sm">
                    <div class="flex gap-8">
                        <span class="text-gray-500">小計（税抜）</span>
                        <span id="total-subtotal" class="w-32 text-right font-medium">¥0</span>
                    </div>
                    <div class="flex gap-8">
                        <span class="text-gray-500">消費税</span>
                        <span id="total-tax" class="w-32 text-right">¥0</span>
                    </div>
                    <div class="flex gap-8 text-base font-bold text-gray-900 border-t border-gray-200 pt-1 mt-1">
                        <span>合計（税込）</span>
                        <span id="total-amount" class="w-32 text-right">¥0</span>
                    </div>
                </div>
            </div>

            {{-- 送信ボタン --}}
            <div class="flex items-center justify-between">
                <a href="{{ isset($order) ? route('orders.show', $order) : route('orders.index') }}"
                   class="text-sm text-gray-500 hover:underline">キャンセル</a>
                <button type="submit"
                        class="bg-blue-600 text-white text-sm font-medium px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                    {{ isset($order) ? '更新する' : '受注登録' }}
                </button>
            </div>
        </div>
    </div>
</form>

{{-- 商品マスタデータ（JS用） --}}
<script>
const PRODUCTS = @json($products->keyBy('id'));
</script>

@endsection

@push('scripts')
<script>
// ─── 行テンプレート --
function rowHtml(i) {
    return `<tr class="item-row border-b border-gray-100" data-index="${i}">
      <td class="py-2 text-gray-400 text-xs">${i+1}</td>
      <td class="py-2 pr-2">
        <select class="product-select border border-gray-200 rounded px-2 py-1 text-xs w-36 mr-1"
                onchange="fillProduct(this, ${i})">
          <option value="">-- 商品選択 --</option>
          ${Object.values(PRODUCTS).map(p => `<option value="${p.id}" data-price="${p.unit_price}" data-unit="${p.unit}" data-tax="${p.tax_rate}">${p.name}</option>`).join('')}
        </select>
        <input type="text" name="items[${i}][product_name]" placeholder="商品名"
               class="border border-gray-200 rounded px-2 py-1 text-xs w-40 product-name">
        <input type="hidden" name="items[${i}][product_id]" class="product-id">
      </td>
      <td class="py-2 pr-2"><input type="number" name="items[${i}][unit_price]" value="0" min="0"
             class="border border-gray-200 rounded px-2 py-1 text-xs w-24 text-right unit-price"
             oninput="calcRow(${i})"></td>
      <td class="py-2 pr-2"><input type="number" name="items[${i}][quantity]" value="1" min="1"
             class="border border-gray-200 rounded px-2 py-1 text-xs w-16 text-right qty"
             oninput="calcRow(${i})"></td>
      <td class="py-2 pr-2 text-center">
        <select name="items[${i}][tax_rate]" class="border border-gray-200 rounded px-1 py-1 text-xs tax-rate"
                onchange="calcRow(${i})">
          <option value="10" selected>10%</option>
          <option value="8">8%</option>
          <option value="0">非課税</option>
        </select>
      </td>
      <td class="py-2 text-right text-xs font-medium line-sub">¥0</td>
      <td class="py-2 text-center">
        <button type="button" onclick="removeRow(this)" class="text-red-400 hover:text-red-600 text-xs">✕</button>
      </td>
    </tr>`;
}

function fillProduct(sel, i) {
    const opt = sel.options[sel.selectedIndex];
    if (!opt.value) return;
    const row = sel.closest('tr');
    row.querySelector('.product-name').value = opt.text;
    row.querySelector('.product-id').value   = opt.value;
    row.querySelector('.unit-price').value   = opt.dataset.price;
    row.querySelector('.tax-rate').value     = opt.dataset.tax;
    calcRow(i);
}

function calcRow(i) {
    const row   = document.querySelector(`[data-index="${i}"]`);
    const price = parseFloat(row.querySelector('.unit-price').value) || 0;
    const qty   = parseFloat(row.querySelector('.qty').value) || 0;
    const sub   = price * qty;
    row.querySelector('.line-sub').textContent = '¥' + sub.toLocaleString();
    calcTotals();
}

function calcTotals() {
    let subtotal = 0, tax = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const price = parseFloat(row.querySelector('.unit-price').value) || 0;
        const qty   = parseFloat(row.querySelector('.qty').value) || 0;
        const rate  = parseFloat(row.querySelector('.tax-rate').value) || 0;
        const sub   = price * qty;
        subtotal += sub;
        tax      += Math.floor(sub * rate / 100);
    });
    document.getElementById('total-subtotal').textContent = '¥' + subtotal.toLocaleString();
    document.getElementById('total-tax').textContent      = '¥' + tax.toLocaleString();
    document.getElementById('total-amount').textContent   = '¥' + (subtotal + tax).toLocaleString();
}

let rowCount = document.querySelectorAll('.item-row').length;

document.getElementById('add-row').onclick = () => {
    document.getElementById('items-body').insertAdjacentHTML('beforeend', rowHtml(rowCount++));
    calcTotals();
};

function removeRow(btn) {
    btn.closest('tr').remove();
    calcTotals();
}

// 初期計算
document.querySelectorAll('.item-row').forEach((_, i) => calcRow(i));
</script>
@endpush
