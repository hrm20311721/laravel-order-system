<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_name',
        'unit_price', 'unit', 'quantity', 'tax_rate',
        'line_subtotal', 'line_tax', 'line_total', 'sort_order',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // 行金額を自動計算してセット
    public function calculateLines(): void
    {
        $subtotal = $this->unit_price * $this->quantity;
        $tax      = (int) floor($subtotal * $this->tax_rate / 100);
        $this->line_subtotal = $subtotal;
        $this->line_tax      = $tax;
        $this->line_total    = $subtotal + $tax;
    }
}
