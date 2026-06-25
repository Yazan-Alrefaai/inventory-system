<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // products and stock_movements already converted in a previous partial run.
        // Only sale_items.qty remains as INTEGER.

        \DB::statement('PRAGMA foreign_keys = OFF');

        \DB::statement('ALTER TABLE sale_items RENAME TO _sale_items_old');
        \DB::statement('
            CREATE TABLE sale_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                sale_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                qty DECIMAL(12,3) NOT NULL,
                price DECIMAL(14,2) NOT NULL,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
            )
        ');
        \DB::statement('INSERT INTO sale_items SELECT * FROM _sale_items_old');
        \DB::statement('DROP TABLE _sale_items_old');

        \DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        \DB::statement('PRAGMA foreign_keys = OFF');

        \DB::statement('ALTER TABLE sale_items RENAME TO _sale_items_old');
        \DB::statement('
            CREATE TABLE sale_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                sale_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                qty INTEGER NOT NULL,
                price DECIMAL(14,2) NOT NULL,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
            )
        ');
        \DB::statement('INSERT INTO sale_items SELECT * FROM _sale_items_old');
        \DB::statement('DROP TABLE _sale_items_old');

        \DB::statement('PRAGMA foreign_keys = ON');
    }
};
