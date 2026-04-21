<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number', 'customer_id', 'status',
        'order_date', 'delivery_date', 'shipped_date',
        'subtotal', 'tax_amount', 'total_amount',
        'shipping_address', 'notes', 'created_by',
    ];

    protected $casts = [
        'order_date'    => 'date',
        'delivery_date' => 'date',
        'shipped_date'  => 'date',
    ];

    // ステータス日本語ラベル
    public const STATUS_LABELS = [
        'quote'     => '見積',
        'ordered'   => '受注',
        'shipping'  => '出荷中',
        'completed' => '完了',
        'cancelled' => 'キャンセル',
    ];

    // ステータスごとのバッジ色（Tailwind）
    public const STATUS_COLORS = [
        'quote'     => 'bg-gray-100 text-gray-700',
        'ordered'   => 'bg-blue-100 text-blue-700',
        'shipping'  => 'bg-yellow-100 text-yellow-700',
        'completed' => 'bg-green-100 text-green-700',
        'cancelled' => 'bg-red-100 text-red-700',
    ];

    // ──────────────────────────────────────────
    // Relations
    // ──────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class)->orderBy('sort_order');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    // ──────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'bg-gray-100 text-gray-700';
    }

    public function getCanInvoiceAttribute(): bool
    {
        return in_array($this->status, ['ordered', 'shipping', 'completed'])
            && ! $this->invoice()->exists();
    }

    // ──────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────

    public function recalculateTotals(): void
    {
        $subtotal   = $this->items->sum('line_subtotal');
        $taxAmount  = $this->items->sum('line_tax');
        $this->update([
            'subtotal'     => $subtotal,
            'tax_amount'   => $taxAmount,
            'total_amount' => $subtotal + $taxAmount,
        ]);
    }

    // ──────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('order_date', now()->month)
                     ->whereYear('order_date', now()->year);
    }
}
