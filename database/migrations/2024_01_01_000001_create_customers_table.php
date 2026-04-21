<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('顧客コード');
            $table->string('name', 100)->comment('顧客名');
            $table->string('name_kana', 100)->nullable()->comment('フリガナ');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('zip_code', 10)->nullable();
            $table->string('prefecture', 20)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('contact_person', 50)->nullable()->comment('担当者名');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
