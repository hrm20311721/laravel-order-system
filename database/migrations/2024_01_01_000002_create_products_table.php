<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique()->comment('商品コード');
            $table->string('name', 150)->comment('商品名');
            $table->text('description')->nullable();
            $table->unsignedInteger('unit_price')->comment('単価（円）');
            $table->string('unit', 20)->default('個')->comment('単位');
            $table->unsignedTinyInteger('tax_rate')->default(10)->comment('消費税率(%)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
