<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockMovementController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('products', ProductController::class);



Route::get('stock/in',      [StockMovementController::class, 'createIn'])->name('stock.in');
Route::post('stock/in',     [StockMovementController::class, 'storeIn'])->name('stock.in.store');
Route::get('stock/out',     [StockMovementController::class, 'createOut'])->name('stock.out');
Route::post('stock/out',    [StockMovementController::class, 'storeOut'])->name('stock.out.store');
Route::get('stock/history', [StockMovementController::class, 'history'])->name('stock.history');



Route::get('debts',                   [DebtController::class, 'index'])->name('debts.index');
Route::get('debts/{movement}',        [DebtController::class, 'show'])->name('debts.show');
Route::post('debts/{movement}/pay',   [DebtController::class, 'pay'])->name('debts.pay');

Route::post('settings/rate',   [SettingController::class, 'updateRate'])->name('settings.rate');
Route::get('settings/backup', [SettingController::class, 'backup'])->name('settings.backup');

Route::get('reports',             [ReportController::class, 'index'])->name('reports.index');
Route::get('reports/export',      [ReportController::class, 'export'])->name('reports.export');

Route::get('stock/{movement}/receipt', [StockMovementController::class, 'receipt'])->name('stock.receipt');
Route::delete('stock/{movement}', [StockMovementController::class, 'destroy'])->name('stock.destroy');

Route::get('categories',          [CategoryController::class, 'index'])->name('categories.index');
Route::post('categories',         [CategoryController::class, 'store'])->name('categories.store');
Route::put('categories/{category}',    [CategoryController::class, 'update'])->name('categories.update');
Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

Route::get('expenses',                [ExpenseController::class, 'index'])->name('expenses.index');
Route::post('expenses',               [ExpenseController::class, 'store'])->name('expenses.store');
Route::post('expenses/opening',       [ExpenseController::class, 'setOpening'])->name('expenses.opening');
Route::post('expenses/exchange',         [ExpenseController::class, 'storeExchange'])->name('expenses.exchange');
Route::post('expenses/exchange-reverse', [ExpenseController::class, 'storeExchangeReverse'])->name('expenses.exchange.reverse');
Route::delete('expenses/{expense}',   [ExpenseController::class, 'destroy'])->name('expenses.destroy');

Route::get('sales',          [SaleController::class, 'index'])->name('sales.index');
Route::get('sales/create',   [SaleController::class, 'create'])->name('sales.create');
Route::post('sales',         [SaleController::class, 'store'])->name('sales.store');
Route::get('sales/{sale}',    [SaleController::class, 'show'])->name('sales.show');
Route::post('sales/{sale}/pay',[SaleController::class, 'pay'])->name('sales.pay');
Route::put('sales/{sale}',    [SaleController::class, 'update'])->name('sales.update');
Route::delete('sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');
