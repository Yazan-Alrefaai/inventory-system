<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * إصلاح لمرة واحدة: قاعدة بيانات تضررت من تحويل qty الجزئي.
 * - يعيد سجلات الحركة العالقة في _stock_movements_old إلى stock_movements
 * - يعيد بناء debt_payments إذا كان مفتاحها الأجنبي يشير للجدول القديم
 * - ينظّف قيم sell_price النصية غير الصالحة
 * آمن تماماً على قواعد البيانات السليمة (لا يفعل شيئاً).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');

        // 1) استرجاع الحركات العالقة في الجدول القديم
        $oldExists = DB::selectOne("SELECT name FROM sqlite_master WHERE type='table' AND name='_stock_movements_old'");
        if ($oldExists) {
            DB::statement('
                INSERT INTO stock_movements (id, sale_id, product_id, type, qty, price, note, currency, exchange_rate, customer_name, amount_paid, is_credit, created_at, updated_at)
                SELECT o.id, o.sale_id, o.product_id, o.type, o.qty, o.price, o.note, o.currency, o.exchange_rate, o.customer_name, o.amount_paid, o.is_credit, o.created_at, o.updated_at
                FROM _stock_movements_old o
                WHERE o.id NOT IN (SELECT id FROM stock_movements)
            ');
        }

        // 2) إعادة بناء debt_payments إذا كان FK يشير إلى الجدول القديم
        $dpSchema = DB::selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name='debt_payments'");
        if ($dpSchema && str_contains($dpSchema->sql, '_stock_movements_old')) {
            DB::statement('ALTER TABLE debt_payments RENAME TO _debt_payments_tmp');
            DB::statement("
                CREATE TABLE debt_payments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    movement_id INTEGER NOT NULL,
                    amount NUMERIC NOT NULL,
                    note TEXT,
                    created_at DATETIME,
                    updated_at DATETIME,
                    pay_currency VARCHAR NOT NULL DEFAULT 'SYP',
                    exchange_rate NUMERIC,
                    FOREIGN KEY (movement_id) REFERENCES stock_movements(id) ON DELETE CASCADE
                )
            ");
            DB::statement('INSERT INTO debt_payments SELECT id, movement_id, amount, note, created_at, updated_at, pay_currency, exchange_rate FROM _debt_payments_tmp');
            DB::statement('DROP TABLE _debt_payments_tmp');
        }

        if ($oldExists) {
            DB::statement('DROP TABLE _stock_movements_old');
        }

        // 3) تنظيف قيم sell_price النصية (مخلفات تجارب قديمة)
        if (Schema::hasColumn('products', 'sell_price')) {
            DB::statement("UPDATE products SET sell_price = 0 WHERE typeof(sell_price) = 'text'");
        }

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        // إصلاح بيانات لمرة واحدة — لا تراجع
    }
};
