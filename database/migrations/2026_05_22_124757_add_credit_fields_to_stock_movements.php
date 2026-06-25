<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('currency', 3)->default('USD')->after('price');       // USD or SYP
            $table->decimal('exchange_rate', 12, 2)->nullable()->after('currency'); // ليرة سورية لكل دولار
            $table->string('customer_name')->nullable()->after('exchange_rate');
            $table->decimal('amount_paid', 12, 2)->nullable()->after('customer_name');
            $table->boolean('is_credit')->default(false)->after('amount_paid');
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate', 'customer_name', 'amount_paid', 'is_credit']);
        });
    }
};
