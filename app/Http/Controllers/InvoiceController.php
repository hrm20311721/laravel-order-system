<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $invoiceService) {}

    // ──────────────────────────────────────────
    // 一覧
    // ──────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Invoice::with('customer')
            ->latest('issue_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('invoice_number', 'like', "%{$q}%")
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$q}%"));
            });
        }

        $invoices  = $query->paginate(20)->withQueryString();
        $customers = \App\Models\Customer::active()->orderBy('name')->get(['id', 'name']);

        return view('invoices.index', compact('invoices', 'customers'));
    }

    // ──────────────────────────────────────────
    // 詳細
    // ──────────────────────────────────────────

    public function show(Invoice $invoice)
    {
        $invoice->load(['order.items', 'customer']);
        return view('invoices.show', compact('invoice'));
    }

    // ──────────────────────────────────────────
    // 受注から請求書発行
    // ──────────────────────────────────────────

    public function createFromOrder(Request $request, Order $order)
    {
        $request->validate([
            'issue_date' => 'required|date',
            'due_date'   => 'required|date|after_or_equal:issue_date',
            'notes'      => 'nullable|string|max:1000',
        ]);

        try {
            $invoice = $this->invoiceService->createFromOrder($order, $request->only('issue_date', 'due_date', 'notes'));
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "請求書 {$invoice->invoice_number} を発行しました。");
    }

    // ──────────────────────────────────────────
    // PDF ダウンロード
    // ──────────────────────────────────────────

    public function downloadPdf(Invoice $invoice)
    {
        // PDFが存在しなければ再生成
        if (! $invoice->pdf_path || ! Storage::disk('local')->exists($invoice->pdf_path)) {
            $this->invoiceService->generatePdf($invoice->fresh());
            $invoice->refresh();
        }

        return Storage::disk('local')->download(
            $invoice->pdf_path,
            "{$invoice->invoice_number}.pdf"
        );
    }

    // ──────────────────────────────────────────
    // メール送信
    // ──────────────────────────────────────────

    public function send(Invoice $invoice)
    {
        try {
            $this->invoiceService->send($invoice);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', '請求書メールをキューに追加しました。');
    }

    // ──────────────────────────────────────────
    // 入金確認
    // ──────────────────────────────────────────

    public function markPaid(Request $request, Invoice $invoice)
    {
        $request->validate(['paid_at' => 'nullable|date']);

        $this->invoiceService->markAsPaid($invoice, $request->paid_at);

        return back()->with('success', '入金確認済みにしました。');
    }
}
