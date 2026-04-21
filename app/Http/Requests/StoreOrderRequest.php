<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'customer_id'          => 'required|exists:customers,id',
            'status'               => 'nullable|in:quote,ordered',
            'order_date'           => 'required|date',
            'delivery_date'        => 'nullable|date|after_or_equal:order_date',
            'shipping_address'     => 'nullable|string|max:255',
            'notes'                => 'nullable|string|max:2000',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'nullable|exists:products,id',
            'items.*.product_name' => 'required|string|max:150',
            'items.*.unit_price'   => 'required|integer|min:0',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.unit'         => 'nullable|string|max:20',
            'items.*.tax_rate'     => 'nullable|integer|in:0,8,10',
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_id'  => '顧客',
            'order_date'   => '受注日',
            'items'        => '明細',
            'items.*.product_name' => '商品名',
            'items.*.unit_price'   => '単価',
            'items.*.quantity'     => '数量',
        ];
    }
}
