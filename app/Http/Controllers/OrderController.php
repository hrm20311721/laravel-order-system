<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderController extends Controller
{
    // ──────────────────────────────────────────
    // 一覧
    // ──────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Order::with('customer')
            ->latest('order_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('date_from')) {
            $query->where('order_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('order_date', '<=', $request->date_to);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('order_number', 'like', "%{$q}%")
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$q}%"));
            });
        }

        $orders    = $query->paginate(20)->withQueryString();
        $customers = Customer::active()->orderBy('name')->get(['id', 'name']);

        return view('orders.index', compact('orders', 'customers'));
    }

    // ──────────────────────────────────────────
    // 新規作成フォーム
    // ──────────────────────────────────────────

    public function create()
    {
        $customers = Customer::active()->orderBy('name')->get(['id', 'name', 'email']);
        $products  = Product::active()->orderBy('name')->get(['id', 'code', 'name', 'unit_price', 'unit', 'tax_rate']);

        return view('orders.create', compact('customers', 'products'));
    }

    // ──────────────────────────────────────────
    // 保存
    // ──────────────────────────────────────────

    public function store(StoreOrderRequest $request)
    {
        $order = DB::transaction(function () use ($request) {
            $order = Order::create([
                'order_number'    => $this->generateOrderNumber(),
                'customer_id'     => $request->customer_id,
                'status'          => $request->status ?? 'ordered',
                'order_date'      => $request->order_date,
                'delivery_date'   => $request->delivery_date,
                'shipping_address'=> $request->shipping_address,
                'notes'           => $request->notes,
                'created_by'      => auth()->id(),
                'subtotal'        => 0,
                'tax_amount'      => 0,
                'total_amount'    => 0,
            ]);

            $this->syncItems($order, $request->items ?? []);
            $order->recalculateTotals();

            return $order;
        });

        return redirect()->route('orders.show', $order)
            ->with('success', "受注 {$order->order_number} を登録しました。");
    }

    // ──────────────────────────────────────────
    // 詳細
    // ──────────────────────────────────────────

    public function show(Order $order)
    {
        $order->load(['customer', 'items.product', 'invoice']);
        return view('orders.show', compact('order'));
    }

    // ──────────────────────────────────────────
    // 編集フォーム
    // ──────────────────────────────────────────

    public function edit(Order $order)
    {
        abort_if(in_array($order->status, ['completed', 'cancelled']), 403, '完了・キャンセル済みの受注は編集できません。');

        $order->load('items');
        $customers = Customer::active()->orderBy('name')->get(['id', 'name', 'email']);
        $products  = Product::active()->orderBy('name')->get(['id', 'code', 'name', 'unit_price', 'unit', 'tax_rate']);

        return view('orders.edit', compact('order', 'customers', 'products'));
    }

    // ──────────────────────────────────────────
    // 更新
    // ──────────────────────────────────────────

    public function update(UpdateOrderRequest $request, Order $order)
    {
        DB::transaction(function () use ($request, $order) {
            $order->update($request->only([
                'customer_id', 'status', 'order_date',
                'delivery_date', 'shipped_date',
                'shipping_address', 'notes',
            ]));

            $this->syncItems($order, $request->items ?? []);
            $order->recalculateTotals();
        });

        return redirect()->route('orders.show', $order)
            ->with('success', '受注を更新しました。');
    }

    // ──────────────────────────────────────────
    // ステータス変更（AJAXまたはフォームPOST）
    // ──────────────────────────────────────────

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate(['status' => 'required|in:quote,ordered,shipping,completed,cancelled']);

        $order->update(['status' => $request->status]);

        if ($request->expectsJson()) {
            return response()->json(['status' => $order->status_label]);
        }

        return back()->with('success', 'ステータスを更新しました。');
    }

    // ──────────────────────────────────────────
    // 削除（ソフトデリート）
    // ──────────────────────────────────────────

    public function destroy(Order $order)
    {
        abort_if($order->invoice()->exists(), 422, '請求書が発行済みのため削除できません。');

        $order->delete();

        return redirect()->route('orders.index')
            ->with('success', '受注を削除しました。');
    }

    // ──────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────

    private function generateOrderNumber(): string
    {
        $prefix = 'ORD-' . now()->format('Ym');
        $latest = Order::where('order_number', 'like', $prefix . '-%')
            ->orderByDesc('order_number')->value('order_number');
        $seq = $latest ? ((int) substr($latest, -4)) + 1 : 1;
        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    private function syncItems(Order $order, array $items): void
    {
        $order->items()->delete();

        foreach ($items as $i => $data) {
            if (empty($data['product_name']) || empty($data['quantity'])) {
                continue;
            }
            $item = new OrderItem([
                'product_id'   => $data['product_id'] ?? null,
                'product_name' => $data['product_name'],
                'unit_price'   => (int) $data['unit_price'],
                'unit'         => $data['unit'] ?? '個',
                'quantity'     => (int) $data['quantity'],
                'tax_rate'     => (int) ($data['tax_rate'] ?? 10),
                'sort_order'   => $i,
            ]);
            $item->calculateLines();
            $order->items()->save($item);
        }
    }
}
