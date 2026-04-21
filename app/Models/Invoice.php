<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number', 'order_id', 'customer_id',
        'issue_date', 'due_date',
        'subtotal', 'tax_amount', 'total_amount',
        'status', 'sent_at', 'paid_at', 'pdf_path', 'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date'   => 'date',
        'sent_at'    => 'datetime',
        'paid_at'    => 'date',
    ];

    public const STATUS_LABELS = [
        'draft'     => '下書き',
        'sent'      => '送付済',
        'paid'      => '入金確認',
        'overdue'   => '入金遅延',
        'cancelled' => 'キャンセル',
    ];

    public const STATUS_COLORS = [
        'draft'     => 'bg-gray-100 text-gray-700',
        'sent'      => 'bg-blue-100 text-blue-700',
        'paid'      => 'bg-green-100 text-green-700',
        'overdue'   => 'bg-red-100 text-red-700',
        'cancelled' => 'bg-gray-100 text-gray-400',
    ];

    // ──────────────────────────────────────────
    // Relations
    // ──────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
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

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'sent' && $this->due_date->isPast();
    }

    // ──────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['sent', 'overdue']);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('issue_date', now()->month)
                     ->whereYear('issue_date', now()->year);
    }
}
