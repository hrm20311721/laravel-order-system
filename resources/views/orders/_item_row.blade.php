<tr class="item-row border-b border-gray-100" data-index="{{ $i }}">
    <td class="py-2 text-gray-400 text-xs">{{ $i + 1 }}</td>
    <td class="py-2 pr-2">
        <select class="product-select border border-gray-200 rounded px-2 py-1 text-xs w-36 mr-1"
            onchange="fillProduct(this, {{ $i }})">
            <option value="">-- 商品選択 --</option>
            @foreach ($products as $p)
            <option value="{{ $p->id }}" data-price="{{ $p->unit_price }}" data-unit="{{ $p->unit }}"
                data-tax="{{ $p->tax_rate }}" {{ ($item['product_id'] ?? '' )==$p->id ? 'selected' : '' }}>
                {{ $p->name }}
            </option>
            @endforeach
        </select>
        <input type="text" name="items[{{ $i }}][product_name]" value="{{ $item['product_name'] ?? '' }}"
            placeholder="商品名" class="border border-gray-200 rounded px-2 py-1 text-xs w-40 product-name">
        <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $item['product_id'] ?? '' }}"
            class="product-id">
    </td>
    <td class="py-2 pr-2">
        <input type="number" name="items[{{ $i }}][unit_price]" value="{{ $item['unit_price'] ?? 0 }}" min="0"
            class="border border-gray-200 rounded px-2 py-1 text-xs w-24 text-right unit-price"
            oninput="calcRow({{ $i }})">
    </td>
    <td class="py-2 pr-2">
        <input type="number" name="items[{{ $i }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" min="1"
            class="border border-gray-200 rounded px-2 py-1 text-xs w-16 text-right qty" oninput="calcRow({{ $i }})">
    </td>
    <td class="py-2 pr-2 text-center">
        <select name="items[{{ $i }}][tax_rate]" class="border border-gray-200 rounded px-1 py-1 text-xs tax-rate"
            onchange="calcRow({{ $i }})">
            <option value="10" {{ ($item['tax_rate'] ?? 10)==10 ? 'selected' : '' }}>10%</option>
            <option value="8" {{ ($item['tax_rate'] ?? 10)==8 ? 'selected' : '' }}>8%</option>
            <option value="0" {{ ($item['tax_rate'] ?? 10)==0 ? 'selected' : '' }}>非課税</option>
        </select>
    </td>
    <td class="py-2 text-right text-xs font-medium line-sub">
        ¥{{ number_format(($item['line_subtotal'] ?? 0)) }}
    </td>
    <td class="py-2 text-center">
        <button type="button" onclick="removeRow(this)" class="text-red-400 hover:text-red-600 text-xs">✕</button>
    </td>
</tr>
