<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('debt_payments', function (Blueprint $table) {
            $table->string('pay_currency', 3)->default('SYP')->after('amount');
            $table->decimal('exchange_rate', 12, 2)->nullable()->after('pay_currency');
        });
    }
    public function down(): void {
        Schema::table('debt_payments', function (Blueprint $table) {
            $table->dropColumn(['pay_currency', 'exchange_rate']);
        });
    }
};
