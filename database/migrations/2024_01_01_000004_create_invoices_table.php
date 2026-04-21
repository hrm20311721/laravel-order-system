<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 25)->unique()->comment('請求書番号 例:INV-20240101-0001');
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->date('issue_date')->comment('発行日');
            $table->date('due_date')->comment('支払期限');
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('tax_amount');
            $table->unsignedInteger('total_amount');
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])
                  ->default('draft');
            $table->datetime('sent_at')->nullable()->comment('送付日時');
            $table->date('paid_at')->nullable()->comment('入金確認日');
            $table->string('pdf_path', 500)->nullable()->comment('生成PDFのパス');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'status']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
