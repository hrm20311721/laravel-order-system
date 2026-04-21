<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 20)->unique()->comment('受注番号 例:ORD-20240101-0001');
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->enum('status', [
                'quote',        // 見積
                'ordered',      // 受注
                'shipping',     // 出荷中
                'completed',    // 完了
                'cancelled',    // キャンセル
            ])->default('quote');
            $table->date('order_date')->comment('受注日');
            $table->date('delivery_date')->nullable()->comment('納品希望日');
            $table->date('shipped_date')->nullable()->comment('出荷日');
            $table->unsignedInteger('subtotal')->default(0)->comment('小計（税抜）');
            $table->unsignedInteger('tax_amount')->default(0)->comment('消費税合計');
            $table->unsignedInteger('total_amount')->default(0)->comment('税込合計');
            $table->string('shipping_address', 255)->nullable()->comment('配送先（顧客と異なる場合）');
            $table->text('notes')->nullable()->comment('備考');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'status']);
            $table->index('order_date');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name', 150)->comment('商品名（スナップショット）');
            $table->unsignedInteger('unit_price')->comment('単価（税抜）');
            $table->string('unit', 20)->default('個');
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->unsignedTinyInteger('tax_rate')->default(10);
            $table->unsignedInteger('line_subtotal')->comment('行小計（税抜）');
            $table->unsignedInteger('line_tax')->comment('行消費税');
            $table->unsignedInteger('line_total')->comment('行合計（税込）');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
