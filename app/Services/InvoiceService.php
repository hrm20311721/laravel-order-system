<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Mail\InvoiceMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class InvoiceService
{
    /**
     * 受注から請求書を生成する
     */
    public function createFromOrder(Order $order, array $options = []): Invoice
    {
        if ($order->invoice()->exists()) {
            throw new \RuntimeException("受注 {$order->order_number} にはすでに請求書が存在します。");
        }

        $issueDate = Carbon::parse($options['issue_date'] ?? today());
        $dueDate   = Carbon::parse($options['due_date']   ?? today()->addDays(30));

        $invoice = Invoice::create([
            'invoice_number' => $this->generateInvoiceNumber($issueDate),
            'order_id'       => $order->id,
            'customer_id'    => $order->customer_id,
            'issue_date'     => $issueDate,
            'due_date'       => $dueDate,
            'subtotal'       => $order->subtotal,
            'tax_amount'     => $order->tax_amount,
            'total_amount'   => $order->total_amount,
            'status'         => 'draft',
            'notes'          => $options['notes'] ?? null,
        ]);

        // PDF を即時生成して保存
        $this->generatePdf($invoice);

        return $invoice->fresh(['order.items', 'customer']);
    }

    /**
     * PDF を生成して Storage に保存し、パスを Invoice に記録する
     */
    public function generatePdf(Invoice $invoice): string
    {
        $invoice->load(['order.items', 'customer']);

        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $invoice])
                  ->setPaper('a4', 'portrait');

        $path = "invoices/{$invoice->invoice_number}.pdf";
        Storage::disk('local')->put($path, $pdf->output());

        $invoice->update(['pdf_path' => $path]);

        return $path;
    }

    /**
     * 請求書PDFをメールで送付する
     */
    public function send(Invoice $invoice): void
    {
        if (! $invoice->customer->email) {
            throw new \RuntimeException('顧客メールアドレスが登録されていません。');
        }

        // PDFが未生成なら生成
        if (! $invoice->pdf_path || ! Storage::disk('local')->exists($invoice->pdf_path)) {
            $this->generatePdf($invoice);
            $invoice->refresh();
        }

        Mail::to($invoice->customer->email)
            ->queue(new InvoiceMail($invoice));

        $invoice->update([
            'status'  => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * 入金確認処理
     */
    public function markAsPaid(Invoice $invoice, ?string $paidDate = null): void
    {
        $invoice->update([
            'status'  => 'paid',
            'paid_at' => $paidDate ?? today(),
        ]);
    }

    /**
     * 期限切れ請求書を overdue に更新（スケジューラーから呼ぶ）
     */
    public function markOverdue(): int
    {
        return Invoice::where('status', 'sent')
            ->where('due_date', '<', today())
            ->update(['status' => 'overdue']);
    }

    // ──────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────

    private function generateInvoiceNumber(Carbon $date): string
    {
        $prefix = 'INV-' . $date->format('Ym');

        // 当月の最大連番を取得して +1
        $latest = Invoice::where('invoice_number', 'like', $prefix . '-%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $seq = $latest ? ((int) substr($latest, -4)) + 1 : 1;

        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
