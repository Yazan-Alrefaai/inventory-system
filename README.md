# Inventory & Warehouse Management System

A full-featured inventory, sales, and accounting system for small businesses, built with **Laravel 13** and **PHP 8.3**. It handles the complete cycle of a shop or warehouse — from receiving stock, to selling on cash or credit, to tracking debts, expenses, and daily profit — all from a clean, Arabic (RTL) interface designed for non-technical users.

> Built for a real shop operating in a multi-currency environment (USD / SYP), where customers frequently buy on credit and exchange rates change daily.

---

## Features

### 📦 Products & Inventory

- Product catalog with categories, buy price, sell price, and quantity
- **Low-stock alerts** based on a configurable minimum quantity per product
- Live inventory valuation (total stock value at both cost and selling price)

### 🔄 Stock Movements

- Record stock **in** (purchases) and **out** (sales/withdrawals)
- Full movement history with a searchable, filterable log
- Printable receipts for each movement

### 🧾 Sales & Payments

- Multi-line sales (multiple products per invoice)
- **Partial payments** — record a sale now, collect the balance later
- Per-sale payment history

### 💳 Debts & Credit

- Track money owed by customers who buy on credit
- Record debt payments over time and watch balances settle automatically

### 💱 Multi-Currency

- Works in **USD and SYP** side by side
- A single, easily-updated exchange rate drives all conversions
- Dedicated currency-exchange transactions (buy/sell foreign currency)

### 💰 Expenses & Cash

- Log business expenses by type
- Set an opening cash balance
- Daily cash and profit tracking

### 📊 Dashboard & Reports

- KPI dashboard: inventory value, **gross profit today**, week-over-week sales comparison, and **top 5 best-selling products**
- Reports with export
- One-click database **backup**

---

## Tech Stack

| Layer       | Technology                          |
|-------------|-------------------------------------|
| Framework   | Laravel 13                          |
| Language    | PHP 8.3                             |
| Database    | SQLite (zero-config, file-based)    |
| Frontend    | Blade templates + Vite, RTL/Arabic  |
| Build       | Node.js + Vite                      |

SQLite was chosen deliberately so the app can run on a single local machine with **no database server to install or maintain** — ideal for a small shop.

---

## Getting Started

```bash
# 1. Install PHP dependencies
composer install

# 2. Install front-end dependencies and build assets
npm install
npm run build

# 3. Set up environment
cp .env.example .env
php artisan key:generate

# 4. Create the database and run migrations
touch database/database.sqlite
php artisan migrate --seed

# 5. Start the server
php artisan serve
```

Then open <http://localhost:8000>.

> **Windows users:** the included `.bat` scripts (`تثبيت-اول-مرة.bat` = first-time install, `تحديث.bat` = update, `تشغيل.bat` = run) automate the steps above with a double-click.

---

## Notes

- The user interface is in **Arabic (right-to-left)**, tailored for the shop staff who use it daily.
- No real business data, credentials, or `.env` secrets are included in this repository.

---

## License

Released under the [MIT License](https://opensource.org/licenses/MIT).
